RKTable
=====
An HTML Table generating library.
It is a customized version of CI Table Library.
It is designed to use in codeigniter but it can also be use even without codeigniter
It can handle both object and array data.
It has the facility to add edit button for each row.
It is bootstrap ready.


HOW TO USE (Codeigniter)?
1. Copy the file RKTable.php on your applications/libraries folder
2. Load the library into your controller: 
	$this->load->library('RKTable');
3. Optional: Instantiate the library
	a. Set global variable name on your controller
		public $RKtable;
	b. Set the instantiation on your __construct method
		$this->RKtable = new RKTable();
4.  Set the table data
	$this->RKtable->table_data = array($your_data_array);
	Note: The table data can be an array of objects or an array of arrays (associative array).  It can be the result of your database query.

5.  Customize the table properties and generate the table
	echo $this->RKtable->generate();
	
	
HOW TO CUSTOMIZE THE TABLE PROPERTIES?
2. Template
	Assign value to the template variables
		-- You can do it this way: --
		$this->table->template = array('index1'=>'value1','index2'=>'value2', .. 'indexN'=>'valueN');  
		-- OR this way: --
		$this->template['index1'] = 'value1';
		$this->template['index1'] = 'value1';
		
	The index that you can use are:
		table_open, thead_close, heading_row_start, heading_row_end, heading_cell_start,
		heading_cell_end, tbody_open, tbody_close, row_start, row_end, cell_start, cell_end',
		table_close

3. Caption
	syntax: $this->table->caption = "your caption";
	Ex: $this->table->caption ="<h3>Person Details</h3>";
	
4. Table class
	syntax: $this->table->table_class = "your table class";
	Ex: $this->table->table_class = "table table-responsive table-striped";

5.  Heading
	syntax: $this->table->heading = array('Heading1','Heading2','Heading3');  
	Ex: $this->table->heading = array('ID','First Name', 'Middle Name', 'Last Name');

6.  Edit Button
	In setting-up edit button you may supply the following parameters:
	url --  This is where your page will redirect when you click the edit button
	key -- The unique of each data.  This will be commonly IDs such as PersonID, UserID
	position -- The button position either left or right of each row. If this is not specified the button will be set on the left side of each row
	class -- The class of your button.  If you are using bootstrap you can use the btn classes.  If this is not specified the class will be set to the default 'text-info'
	content -- The content of the button.  If not set the content will be set to the default '<span class="glyphicon glyphicon-pencil></span>"'




SAMPLE USAGE:
ON MY CONTROLLER:

Class Main extends CI_Controller
{
	public $table;
	
	public function __construct
	{
		parent::__construct();
		$this->load->library('RKTable');
		$this->table = new RKTable();
	}
	
	public function person()
	{
		$data['person_details'] = $this->person_model->getPerson();
		$this->load->view('person',$data);
	}
}

ON MY VIEW PAGE:

<div class="panel">
	<div class="panel-body">
	<?php 
		if(isset($person_details))
		{
			$this->table->table_data = $person_details;
			$this->table->caption = "<h3>List of Person</h3>";
			$this->table->table_class = "table table-striped table-condensed";
			$this->table->template = array('heading_cell_start'=> '<th class="text-primary">');
			$this->table->btn_edit = array('url'=>'person/action/edit/id', key=>'PersonID');
			echo $this->table->generate();
		}
	?>
	</div>
</div>

