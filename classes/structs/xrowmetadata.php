<?php

class xrowMetaData extends ezcBaseStruct
{
    public $priority;
    public $change;
    public $title;
    public $keywords;
    public $description;

    public function __construct( $title = false, $keywords = false, $description = false, $priority = false, $change = false )
    {
        $this->title = $title;
        $this->keywords = $keywords;
        $this->description = $description;
        $this->priority = $priority;
        $this->change = $change;
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
