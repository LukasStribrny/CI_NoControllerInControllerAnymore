<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
Creator : lukas.stribrny@hotmail.com
This class is about to parse path to requested Controllers class and the method dynamically,
instead of trying to call controller in controller
which makes troubles to codeginiter that is not designed for this purpose.
*/
class My_Router {

	/**
	 * CI_Config class object
	 *
	 * @var	object
	 */
	public $config;

	/**
	 * List of Params
	 *
	 * @var	array
	 */
	public $Params = [];

	/**
	 * Current class name
	 *
	 * @var	string
	 */
	public $class =		'';

	/**
	 * Current method name
	 *
	 * @var	string
	 */
	public $method =	'Index';

	/**
	 * Sub-directory that contains the requested controller class
	 *
	 * @var	string
	 */
	public $directory;

	// --------------------------------------------------------------------

	/**
	 * Class constructor
	 *
	 * Runs the route mapping function.
	 *
	 * @param	array	$routing
	 * @return	void
	 */
	public function __construct($routing = NULL){
		$this->config =& load_class('Config', 'core');
		$this->uri =& load_class('URI', 'core');
		$this->segments = $this->uri->segment_array();
		$this->createParams();
		$this->_set_default_controller();
		log_message('info', 'My_Router Class Initialized');
	}
	
	protected function createParams(){
	if (file_exists(APPPATH.'config/routes.php')){
			include(APPPATH.'config/routes.php');
		}
		if(empty($this->segments)){
			header('Location: ./'.$route['default_controller'].'/'.$this->method.$this->config->item('url_suffix'));
			exit();
		}
		$CF = APPPATH . 'controllers/' .$this->segments[1];
		if(is_file($CF.'.php')){
			$this->set_directory('');
			if(count($this->segments)==1){
				$this->Params['class'] = $this->segments[1];
				$this->Params['method'] = $this->method;
			}else{
				$this->Params['class'] = $this->segments[1];
				$this->Params['method'] = $this->segments[2];
			}
		}elseif(is_dir($CF)){
			$this->Params['Data'] = [];
			$Reg_Key = [];
		foreach($this->segments AS $Seg_Key=>$Seg_Val){
			//Check to see if any value in url is numeric
			if(is_numeric($Seg_Val)){
				//Register numeric key
				$Reg_Key[] = $Seg_Key;
			}
		}
		//Check if we have any registered numeric key
		if(!empty($Reg_Key)){
			//Yes we got the regitered numeric key(s)
			foreach($this->segments AS $Seg_Key=>$Seg_Val){
				/*Get the first registered numeric key
				Separate it to segments -> path to class folder,file class,class method,method numeric data,other data
				
				http://your-cool-server.com/path/to/folder-class/YourClass/YourMethod/MethodNumericData
				
				//Simple Examples :
							
							http://your-cool-server.com/FrontEnd/YourClass/YourMethod/MethodNumericData
							http://your-cool-server.com/BackEnd/YourClass/YourMethod/MethodNumericData
							
							http://your-cool-server.com/FrontEnd/User/Register
							http://your-cool-server.com/BackEnd/User/ShowProfile/365
							
							http://your-cool-server.com/Web/User/Login
							http://your-cool-server.com/Web/User/ShowProfile/365
							http://your-cool-server.com/Web/Content/Page/657
				
				//More complicated Examples : 
							http://your-cool-server.com/FrontEnd/Web/User/Login
							http://your-cool-server.com/BackEnd/User/ShowProfile/365
							
				//Note : ShowProfile is the class method and 365 is the Profile_ID
							http://your-cool-server.com/Public/Web/User/ShowProfile/365/OtherData/ID/248
							http://your-cool-server.com/Public/Web/User/ShowProfile/365/Content/Page/1
							http://your-cool-server.com/Public/Web/User/ShowProfile/365/Content/Page/657
							
							
				//If you try to have url like this : 
							http://your-cool-server.com/BackEnd/User/ShowProfile/UserName
							http://your-cool-server.com/BackEnd/User/ShowProfile/MichaelJakson
					this is not going to be working because it is based on ID
							
				*/
				if($Seg_Key<reset($Reg_Key)){
					$this->Params['Segments'][$Seg_Key] = $Seg_Val;
				}
				if($Seg_Key>=reset($Reg_Key)){
					$this->Params['Data'][] = $Seg_Val;
				}
			}
		}else{
			//There is no any numeric key
			$this->Params['Segments'] = $this->segments;
		}
				
		$this->Params['class_short_path'] = str_replace('/'. basename(implode('/',$this->Params['Segments'])),'',implode('/',$this->Params['Segments']));
		$this->Params['class_full_path'] = str_replace('\\','/',APPPATH) .'controller/'. str_replace('/'. basename(implode('/',$this->Params['Segments'])),'',implode('/',$this->Params['Segments'])).'.php';
		$this->Params['class'] = basename(str_replace('/'. basename(implode('/',$this->Params['Segments'])),'',implode('/',$this->Params['Segments'])));
		$this->Params['method'] =  basename(implode('/',$this->Params['Segments']));
		$this->set_directory(str_replace('/'.basename($this->Params['class_short_path']),'',$this->Params['class_short_path']));
		}else{
			show_404();
		}
	}
	
	protected function _set_default_controller(){
		if(file_exists(APPPATH.'controllers/'.$this->fetch_directory().$this->Params['class'].'.php')){
			$this->set_class($this->Params['class']);
			$this->set_method($this->Params['method']);
		}else{
			show_error('Please make sure the file exist '.$this->fetch_directory() . $this->Params['class'].'.php.');
		}
	}
	// --------------------------------------------------------------------

	/**
	 * Set class name
	 *
	 * @param	string	$class	Class name
	 * @return	void
	 */
	public function set_class($class)
	{
		$this->class = str_replace(array('/', '.'), '', $class);
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch the current class
	 *
	 * @deprecated	3.0.0	Read the 'class' property instead
	 * @return	string
	 */
	public function fetch_class()
	{
		return $this->class;
	}

	// --------------------------------------------------------------------

	/**
	 * Set method name
	 *
	 * @param	string	$method	Method name
	 * @return	void
	 */
	public function set_method($method)
	{
		$this->method = $method;
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch the current method
	 *
	 * @deprecated	3.0.0	Read the 'method' property instead
	 * @return	string
	 */
	public function fetch_method()
	{
		return $this->method;
	}
	// --------------------------------------------------------------------

	/**
	 * Set directory name
	 *
	 * @param	string	$dir	Directory name
	 * @param	bool	$append	Whether we're appending rather than setting the full value
	 * @return	void
	 */
	public function set_directory($dir, $append = FALSE)
	{
		if ($append !== TRUE OR empty($this->directory))
		{
			$this->directory = str_replace('.', '', trim($dir, '/')).'/';
		}
		else
		{
			$this->directory .= str_replace('.', '', trim($dir, '/')).'/';
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch directory
	 *
	 * Feches the sub-directory (if any) that contains the requested
	 * controller class.
	 *
	 * @deprecated	3.0.0	Read the 'directory' property instead
	 * @return	string
	 */
	public function fetch_directory()
	{
		return $this->directory;
	}

}
?>
