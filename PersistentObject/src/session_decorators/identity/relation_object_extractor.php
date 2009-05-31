<?php
/**
 * File containing the ezcPersistentSessionIdentityDecoratorRelationObjectExtractor class.
 *
 * @package PersistentObject
 * @version //autogen//
 * @copyright Copyright (C) 2005-2009 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * Extracts related objects from a generated pre-fetch query.
 *
 * An instance of this class is used in {@link ezcPersistentIdentityMap} to
 * extract related persistent objects from a pre-fetch query generated by
 * {@link ezcPersistentSessionIdentityDecoratorRelationQueryCreator}.
 *
 * @package PersistentObject
 * @version //autogen//
 * @access private
 */
class ezcPersistentIdentityRelationObjectExtractor
{
    /**
     * Definition manager.
     * 
     * @var ezcPersistentDefinitionManager
     */
    protected $defManager;

    /**
     * Identity map.
     * 
     * @var ezcPersistentIdentityMap
     */
    protected $idMap;

    /**
     * Identity session options. 
     * 
     * @var ezcPersistentSessionIdentityDecoratorOptions
     */
    protected $options;

    /**
     * Creates a new object extractor for $idMap on basis of $defManager.
     *
     * Creates a new object extractor which gathers needed object definitions
     * from $defManager and uses $idMap to store the extracted objects and to
     * check if their identities already exist.
     * 
     * @param ezcPersistentIdentityMap $idMap
     * @param ezcPersistentDefinitionManager $defManager 
     */
    public function __construct( ezcPersistentIdentityMap $idMap, ezcPersistentDefinitionManager $defManager, ezcPersistentSessionIdentityDecoratorOptions $options )
    {
        $this->defManager = $defManager;
        $this->idMap      = $idMap;
        $this->options    = $options;
    }

    /**
     * Extracts a single object and its related objects from $stmt.
     *
     * Extracts the object of $class with $id from the result set in $stmt and
     * all of its related objects defined in $relations. The extracted relation
     * sets can be received from the {@link ezcPersistentIdentityMap} given to
     * {@link __construct()}, after this method has finished.
     * 
     * @param PDOStatement $stmt 
     * @param string $class 
     * @param mixed $id 
     * @param array(string=>ezcPersistentRelationFindDefinition) $relations 
     */
    public function extractObjectWithRelatedObjects( PDOStatement $stmt, $class, $id, array $relations )
    {
        $results = $stmt->fetchAll( PDO::FETCH_ASSOC );

        $def = $this->defManager->fetchDefinition( $class );

        $object = $this->idMap->getIdentity( $class, $id );
        if ( $this->options->refetch || $object === null )
        {
            $object = new $class();
            $this->setObjectState(
                $object,
                $def,
                reset( $results )
            );
            $this->idMap->setIdentity( $object );
        }
        
        foreach ( $results as $row )
        {
            $this->extractObjectsRecursive( $row, $relations, $object, array() );
        }
        // @TODO: Return object!
    }

    /**
     * Extracts all objects and their related objects from $stmt.
     *
     * Extracts all objects of the $class defined in $q from $stmt, including
     * all related objects as defined in the $relations property of $q. Returns
     * the set of base objects found by $q.
     * 
     * @param PDOStatement $stmt 
     * @param ezcPersistentFindWithRelationsQuery $q
     * @return array(ezcPersistentObject)
     */
    public function extractObjectsWithRelatedObjects( PDOStatement $stmt, ezcPersistentFindWithRelationsQuery $q )
    {
        $class = $q->className;

        $results = $stmt->fetchAll( PDO::FETCH_ASSOC );

        $def = $this->defManager->fetchDefinition( $class );

        $extractedBaseObjects = array();

        foreach ( $results as $row )
        {
            $baseObjId = $row[$def->idProperty->propertyName];

            if ( !isset( $extractedBaseObjects[$baseObjId] ) )
            {
                $object = new $class();
                $this->setObjectState(
                    $object,
                    $def,
                    $row
                );
                $this->idMap->setIdentity( $object );
                $extractedBaseObjects[$baseObjId] = $object;
            }

            $this->extractObjectsRecursive(
                $row,
                $q->relations,
                $extractedBaseObjects[$baseObjId],
                $q->isRestricted
            );
        }
        
        return $extractedBaseObjects;
    }

    /**
     * Extracts objects recursively from $row.
     *
     * Checks if $row contains new objects defined in $relations. If this is
     * the case, the objects will be extracted and added as related objects of
     * their class for the object of $parentClass with $parentId. If
     * sub-sequent relations exist for an extracted object, this method is
     * called recursively. If $restricted is set to true, named related object
     * sets will be created instead of normal related object sets.
     * 
     * @param array(string=>string) $row 
     * @param array(ezcPersistentRelationFindDefinition) $relations 
     * @param ezcPersistentObject $parent
     * @param bool $restricted
     */
    protected function extractObjectsRecursive( array $row, array $relations, $parent, $restricted = false )
    {
        foreach ( $relations as $tableAlias => $relation )
        {
            $id = $row[
                $this->getColumnAlias(
                    $relation->definition->idProperty->propertyName,
                    $tableAlias
                )
            ];
            
            if ( $id === null )
            {
                // Related object not present, check if a relation is recorded
                // in general, to potentially add an empty set
                if ( $restricted )
                {
                    $relatedObjects = $this->idMap->getRelatedObjectSet(
                        $parent,
                        $tableAlias
                    );
                    if ( $relatedObjects === null )
                    {
                        $this->idMap->setRelatedObjectSet(
                            $parent,
                            array(),
                            $tableAlias
                        );
                    }
                }
                else
                {
                    $relatedObjects = $this->idMap->getRelatedObjects(
                        $parent,
                        $relation->relatedClass,
                        $relation->relationName
                    );
                    if ( $relatedObjects === null )
                    {
                        $this->idMap->setRelatedObjects(
                            $parent,
                            array(),
                            $relation->relatedClass,
                            $relation->relationName
                        );
                    }
                }
                // Skip further processing since this related object did not exist
                continue;
            }

            // Check if object was already extracted
            $object = $this->idMap->getIdentity( $relation->relatedClass, $id );
            if ( $this->options->refetch || $object === null )
            {
                $object = $this->createObject(
                    $row,
                    $tableAlias,
                    $relation
                );
                $this->idMap->setIdentity( $object );
            }

            // Check if relations from $parentClass to $relation->relatedClass
            // were already recorded
            if ( $restricted )
            {
                $relatedObjects = $this->idMap->getRelatedObjectSet(
                    $parent,
                    $tableAlias
                );
            }
            else
            {
                $relatedObjects = $this->idMap->getRelatedObjects(
                    $parent,
                    $relation->relatedClass,
                    $relation->relationName
                );
            }
            if ( $relatedObjects === null )
            {
                // No relation set recorded, create
                $relatedObjects = array();
            }

            // Check if relation itself is already recorded and only set the
            // identities if not
            if ( $this->options->refetch || !isset( $relatedObjects[$id] ) )
            {
                $relatedObjects[$id] = $object;
                // This performs the full setting process on every new object,
                // which is somewhat expensive but not really possible in a
                // different way, since adding new related objects invalidates
                // named related sets.
                if ( $restricted )
                {
                    $this->idMap->setRelatedObjectSet(
                        $parent,
                        $relatedObjects,
                        $tableAlias
                    );
                }
                else
                {
                    $this->idMap->setRelatedObjects(
                        $parent,
                        $relatedObjects,
                        $relation->relatedClass,
                        $relation->relationName
                    );
                }
            }
            
            // Recurse
            $this->extractObjectsRecursive(
                $row,
                $relation->furtherRelations,
                $object,
                $restricted
            );
        }
    }

    /**
     * Creates a new object of $relation->relatedClass with state from $result.
     *
     * Creates a new object of the class defined in $relation->relatedClass and
     * sets its state from the given $result row, as defined in $relation.
     * 
     * @param array(string=>string) $result 
     * @param ezcPersistentRelationFindDefinition $relation 
     * @return ezcPersistentObject
     */
    protected function createObject( array $result, $tableAlias, ezcPersistentRelationFindDefinition $relation )
    {
        $object = new $relation->relatedClass;
        $this->setObjectState(
            $object,
            $relation->definition,
            $result,
            $tableAlias
        );
        return $object;
    }

    /**
     * Sets the state of $object from $result.
     *
     * Sets the state of $object from the $result given, using the $def.
     * 
     * @param ezcPersistentObject $object 
     * @param ezcPersistentObjectDefinition $def 
     * @param array $result 
     * @param string $prefix 
     */
    protected function setObjectState( $object, ezcPersistentObjectDefinition $def, array $result, $prefix = null )
    {
        $state = array(
            $def->idProperty->propertyName => $result[
                $this->getColumnAlias(
                    $def->idProperty->propertyName,
                    $prefix
                )
            ]
        );

        foreach ( $def->properties as $property )
        {
            $state[$property->propertyName] = $result[
                $this->getColumnAlias( $property->propertyName, $prefix )
            ];
        }

        $object->setState( $state );
    }

    /**
     * Returns the column alias for a $column with $prefix.
     * 
     * @param string $column 
     * @param string $prefix 
     * @return string
     */
    protected function getColumnAlias( $column, $prefix = null )
    {
        if ( $prefix === null )
        {
            return $column;
        }
        return sprintf(
            '%s_%s',
            $prefix,
            $column
        );
    }
}

?>
