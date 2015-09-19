<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * HTML Table Generating Class
 *
 * Lets you create tables manually or from database result objects, or arrays.
 * This is a modified version of the CI_Table
 * 
 * @package	CodeIgniter
 * @subpackage	Libraries
 * @category	HTML Tables
 * @author	RedCrisostomo
 */
class RKTable 
{
    public $table_data		= array();
    public $heading		= array();
    public $caption		= NULL;
    public $template		= array();
    public $newline		= "\n";
    public $empty_cells		= "";
    public $table_class         = NULL;
    public $btn_edit            = array();
    public $function		= FALSE;
    private $template_vars       = array('table_open', 'thead_open', 'thead_close', 'heading_row_start', 'heading_row_end', 'heading_cell_start', 'heading_cell_end', 'tbody_open', 'tbody_close', 'row_start', 'row_end', 'cell_start', 'cell_end', 'row_alt_start', 'row_alt_end', 'cell_alt_start', 'cell_alt_end', 'table_close');
    
    /**
    * Build the table
    *
    * @access	public
    * @return	string
    */
    public function generate()
    {
        /*Validate the table data*/
        if(!is_array($this->table_data) OR count($this->table_data) == 0)
        {
            return 'Undefined Table Data';  /*If not array or is empty send a message*/
            exit();  /*Stop the execution*/
        }
        /*Compile the template*/
        $this->_compile_template();
        
        /*If table class is specified*/
        if($this->table_class != NULL)
        {
            /*add class attribute and value to the table open tag*/
            $this->template['table_open'] = str_replace('>', " class='$this->table_class'>", $this->template['table_open']);
        }
        
        /*BEGIN TABLE*/
        $output = $this->template['table_open'];
        $output .= $this->newline;
        
        /*If caption is specified*/
        if($this->caption !=NULL)
        {
            $output .= $this->caption;
            $output .= $this->newline;
        }
        
        /*BEGIN PROCESSING TABLE DATA*/
        foreach($this->table_data as $rows) /*loop through table data arrays*/
        {
            if(is_array($rows)) /* if each row is an array */
            {
               $array_rows[] = $rows; /* put each row into new container*/
               $array_keys = array_keys($rows); /* get the array keys, this will be needed if the table headings are not defined */
            }
            elseif(is_object($rows)) /* if each row is an object */
            {
                $array_rows[] = get_object_vars($rows); /* convert the object into an associative array and put into new container */
                $array_keys = array_keys(get_object_vars($rows)); /* get the array keys, this will be needed if the table headings are not defined */
            }
        }
        
        /*If the heading is not defined set the heading based on array keys of the given table data */
        if($this->_auto_heading() == TRUE)
        {
           $this->heading = $array_keys;
        }
        
        /*If the edit button is set */    
        if(count($this->btn_edit)>0)
        {
            /*If button edit key is not specified*/
            if(isset($this->btn_edit['key']))
            {
                $edit_key = $this->btn_edit['key']; /*put the value into new variable*/
            }
            else /*otherwise send a message*/
            {
                return "Edit button key not specified!";
                exit();
            }
            /** Note: When setting edit button, the key is important as it will serve as the unique identifier to edit the data.
            *   This key should be a field name of the source table, an index of an array, or a unique value on each set of data */
            
            /*If the edit button position is specified and it is set to the right*/
            if(isset($this->btn_edit['position']) && $this->btn_edit['position']=='right') 
            {
                {
                    array_push($this->heading,"Edit"); /* add new heading at the beginning */
                    foreach ($array_rows as $array_columns) /*loop through each rows*/
                    {
                        $key = $array_columns[$edit_key]; /*get the value of specified edit key*/
                        array_push($array_columns, $this->_set_edit_btn($key)); /* add edit button at the beginning of each row */
                        $new_array[] = $array_columns;
                    }
                }
            }
            else /*otherwise if the edit button position is not specified or it is set to the left */
            {
                array_unshift($this->heading, "Edit");

                foreach ($array_rows as $array_columns)
                {
                    $key = $array_columns[$edit_key]; /*get the value of specified edit key*/
                    array_unshift($array_columns, $this->_set_edit_btn($key)); /* add edit button at the end of each row */
                    $new_array[] = $array_columns;
                }
            }

        }
        else
        {
            $new_array = $array_rows;
        }
        
        $table_data = $new_array;
        /*END PROCESSING TABLE DATA*/
        
        
        /* Build the heading */
        $output .= $this->set_heading();
        $output .= $this->newline;
        
        /* Build the table body */
        $output .= $this->template['tbody_open'];
        $output .= $this->newline;
        /* Build the table rows */
        $output .= $this->add_rows($table_data);
        $output .= $this->template['tbody_close'];
        
        $output .= $this->newline;
        $output .= $this->template['table_close'];
        /*END TABLE */
        
        /* Clear table properties before generating the table */
        $this->clear();
        
        return $output;
    }
    /**
     * Set the Table Heading
     * @access public
     * @return string
     */
    public function set_heading()
    {
        $output = $this->template['thead_open'];
        $output .= $this->newline;
        $output .= $this->template['heading_row_start'];
        foreach($this->heading as $heading)
        {
            $output .= $this->template['heading_cell_start'];
            $output .= $heading;
            $output .= $this->template['heading_cell_end'];
        }
        $output .= $this->template['heading_row_end'];
        $output .= $this->newline;
        $output .= $this->template['thead_close'];
        
        return $output;
    }
    
    /**
     * Build the Table Rows
     * @access public
     * @param array $table_data
     * @return boolean/string
     */
    public function add_rows($table_data=array())
    {
        if(!is_array($table_data) OR count($table_data) == 0)
        {
            return FALSE;
        }
        $output = '';
        foreach($table_data as $array_columns)
        {
            $output .= $this->template['row_start'];
            $output .= $this->make_columns($array_columns);
            $output .= $this->template['row_end'];
            $output .= $this->newline;
        }
        return $output;
    }
    
    /**
     * Build the Table Columns
     * @access public
     * @param array $array_columns
     * @return boolean/string
     */
    public function make_columns($array_columns=array())
    {
        if (!is_array($array_columns) OR count($array_columns) == 0)
        {
            return FALSE;
        }
        $output = '';
        foreach($array_columns as $data)
        {
            $output .= $this->template['cell_start'];
            $output .= $data;
            $output .= $this->template['cell_end'];
        }
        
        return $output;
    }
    
    /**
     * Set the edit button
     * @access private
     * @param string $key
     * @return boolean/string
     */
    private function _set_edit_btn($key)
    {
        if(!is_array($this->btn_edit) OR count($this->btn_edit)==0 OR !isset($this->btn_edit['url']))
        {
            return FALSE;
        }
        /*If content is specified*/
        if(isset($this->btn_edit['content']))
        {
            $content = $this->btn_edit['content'];
        }
        else
        {
            $content = "<span class='glyphicon glyphicon-pencil'></span>";
        }
        /*If class is specified*/
        if(isset($this->btn_edit['class']))
        {
            $class = $this->btn_edit['class'];
        }
        else 
        {
            $class = "text-info";
        }
        
        $url = $this->btn_edit['url'];
        $output = "<a href='$url/$key' class='$class' title='Edit'>$content</a>";
        return $output;
    }
    /**
     * Determine if heading is specified
     * @access private
     * @return boolean
     */
    private function _auto_heading()
    {
        if(!is_array($this->heading) OR count($this->heading) == 0)
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }
    /**
     * Clear the table properties
     * @access public
     * @return void
     */
    public function clear()
    {
        $this->table_data = array();
        $this->heading = array();
        $this->table_class = array();
    }
    /**
     * Compile the template
     * @access private
     * @return voide
     */
    private function _compile_template()
    {
        if(!is_array($this->template) OR count($this->template) == 0)
        {
            $this->template = $this->_default_template();
            return;
        }
        $temp = $this->_default_template();
        foreach($this->template_vars as $value)
        {
            if(!isset($this->template[$value])) /*if some table properties are not specified*/
            {
                $this->template[$value] = $temp[$value]; /*set the unspecified properties to the default template*/
            }
        }
    }
    /**
     * Set the default template
     * @access private
     * @return array
     */
    private function _default_template()
    {
        
        return  array (
                        'table_open'        => '<table border="0" cellpadding="4" cellspacing="0">',
                        'thead_open'        => '<thead>',
                        'thead_close'       => '</thead>',
                        'heading_row_start' => '<tr>',
                        'heading_row_end'   => '</tr>',
                        'heading_cell_start'=> '<th>',
                        'heading_cell_end'  => '</th>',
                        'tbody_open'        => '<tbody>',
                        'tbody_close'       => '</tbody>',
                        'row_start'         => '<tr>',
                        'row_end'           => '</tr>',
                        'cell_start'        => '<td>',
                        'cell_end'          => '</td>',
                        'row_alt_start'     => '<tr>',
                        'row_alt_end'       => '</tr>',
                        'cell_alt_start'    => '<td>',
                        'cell_alt_end'      => '</td>',
                        'table_close'       => '</table'
                    );
    }
}