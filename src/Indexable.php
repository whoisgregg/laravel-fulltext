<?php
namespace Swis\LaravelFulltext;

use Swis\LaravelFulltext\ModelObserver;
use Swis\LaravelFulltext\IndexedRecord;

/**
 * Class Indexable
 *
 * @package Swis\LaravelFulltextServiceProvider
 */
trait Indexable {

    /**
     * Boot the trait.
     *
     * @return void
     */
    public static function bootIndexable(){
        static::observe(new ModelObserver);
    }

    public function getIndexContent(){
        return $this->getIndexDataFromColumns($this->indexContentColumns);
    }

    public function getIndexTitle(){
        return $this->getIndexDataFromColumns($this->indexTitleColumns);
    }

    public function indexedRecord(){
        return $this->morphOne('Swis\LaravelFulltext\IndexedRecord', 'indexable');
    }

    public function indexRecord(){
        if(null === $this->indexedRecord){
            $this->indexedRecord = IndexedRecord::create();
            $this->indexedRecord->indexable()->associate($this);
        }
        $this->indexedRecord->updateIndex();
    }

    public function unIndexRecord(){
        if(null !== $this->indexedRecord){
            $this->indexedRecord->delete();
        }
    }

    protected function getIndexDataFromColumns($columns){
        $indexData = [];
        foreach($columns as $column){
            if($this->indexDataIsRelation($column)){
                $indexData[] = $this->getIndexValueFromRelation($column);
            } else {
                $indexData[] = $this->{$column};
            }
        }
        return implode(' ', $indexData);
    }

    /**
     * @param $column
     * @return bool
     */
    protected function indexDataIsRelation($column)
    {
        return (int)strpos($column, '.') > 0;
    }

    /**
     * @param $column
     * @return string
     */
    protected function getIndexValueFromRelation($column)
    {
        list($relation, $column) = explode('.', $column);
        if(is_null($this->{$relation})){
            return '';
        }
        return $this->{$relation}->pluck($column)->implode(', ');
    }
}
