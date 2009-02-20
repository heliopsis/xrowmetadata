<?php

class xrowMetaData extends ezcBaseStruct
{
    public $priority;
    public $change;
    public $title;
    public $keywords;
    public $description;
    public $googlemap;
    public function __construct( $title = false, $keywords = false, $description = false, $priority = false, $change = false, $googlemap = false )
    {
        $this->title = $title;
        $this->keywords = $keywords;
        $this->description = $description;
        $this->priority = $priority;
        $this->change = $change;
        if ( $googlemap === false )
        {
        	$this->googlemap = '1';
        }
    }
    function hasattribute($name)
    {
        $classname = get_class($this);
        $vars = get_class_vars($classname);
        if ( array_key_exists($name,$vars) )
            return true;
        else
            return false;
    }
    function attribute($name)
    {
        return $this->$name;
    }
    /**
     * @return xrowMetaData
     */
    static public function __set_state( array $array )
    {
        return new xrowMetaData( $array['title'], $array['keywords'], $array['description'], $array['priority'], $array['change'] );
    }
}
?>
