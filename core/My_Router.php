<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
Creator : lukas.stribrny@hotmail.com
This class is about to parse path to requested Controllers class and the method dynamically,
instead of trying to call controller in controller
which makes troubles to codeginiter that is not designed for this purpose.
*/

/*
				Get the first registered numeric key
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
					this is not working because it is based on ID
					but i have Enabled alphanumeric url like this ; 
							http://your-cool-server.com/BackEnd/User/ShowProfile/500UserName
							http://your-cool-server.com/BackEnd/User/ShowProfile/500-UserName
							http://your-cool-server.com/BackEnd/User/ShowProfile/500_UserName
							http://your-cool-server.com/BackEnd/User/ShowProfile/MichaelJakson1
							http://your-cool-server.com/BackEnd/User/ShowProfile/MichaelJakson-1
							http://your-cool-server.com/BackEnd/User/ShowProfile/MichaelJakson_1
							since 9.11.2017
*/
class My_Router extends CI_Router {

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
	public $method =	'index';

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
		parent::__construct($routing);
		$this->_set_default_controller();
		log_message('info', 'My_Router Class Initialized');
	}
	
	protected function HeaderDirectToBaseController(){
		if(empty($this->uri->segment_array())){
			header('Location: '.$this->config->item('base_url').$this->default_controller.'/'.$this->method.$this->config->item('url_suffix'));
			exit();
		}
	}
	
	protected function CatchFirstNumKey(){
		$Reg_Key = [];
		foreach($this->uri->segment_array() AS $Seg_Key=>$Seg_Val){
			//Check to see if any value in url is numeric
			if(preg_match ( '/([0-9]+)/', $Seg_Val, $matches )){
				//Register numeric key
				$Reg_Key[] = $Seg_Key;
			}
			if(is_numeric($Seg_Val)){
				//Register numeric key
				$Reg_Key[] = $Seg_Key;
			}
		}
		if(!empty($Reg_Key)){
			return reset($Reg_Key);
		}else{
			return FALSE;
		}
	}
	
	protected function CatchControllerMethod(){
		$this->HeaderDirectToBaseController();
		$FirstNumKey = $this->CatchFirstNumKey();
		$route = [];
		if($FirstNumKey==TRUE){
			foreach($this->uri->segment_array() AS $Seg_Key=>$Seg_Val){
				if($Seg_Key<$FirstNumKey){
					$route[$Seg_Key] = $Seg_Val;
				}
			}
		}else{
			$route = $this->uri->segment_array();
		}
		return $route;
	}
	
	protected function _set_default_controller(){
		$route_current = implode('/',$this->CatchControllerMethod());
		$method = basename($route_current);
		$class = basename(str_replace($method,'',$route_current));
		$route_path = str_replace($class.'/'.$method,'',$route_current);
		$CF = APPPATH . 'controllers/' . $route_path . $class;
		if(file_exists($CF.'.php')){
			$this->set_directory($route_path);
			$this->set_class($class);
			$this->set_method($method);
		}else{
			show_404();
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
