<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Autoform
 *
 * Autobuilder Form (Autoform)
 *
 * @package         Autoform
 * @author          Ardinoto Wahono
 * @version         0.1
 * @copyright        Copyright (c) 2009-2012 Ardinoto Wahono
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

class Autoform
{
    public $element_position = "";
    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->helper('form');
        $this->CI->load->helper('url');
        $this->CI->load->library('form_validation');
        $this->CI->config->load('autoform');

        // Set error delimiters
        $ci_global_error_wrap = explode("|", $this->CI->config->item('ci_global_error_wrap'));
        $this->CI->form_validation->set_error_delimiters($ci_global_error_wrap[0],  $ci_global_error_wrap[1]);

        // Is this the first time the form loaded ?
        $this->first_load = TRUE;
        if (! empty($_POST))
        {
            $this->first_load = FALSE;
        }
    }

    /**
     * Proceed the form and retrieving error message
     */
    function run($table = FALSE, $data = FALSE)
    {
    	if (! $table) // only validate it
    	{
    		if ($this->CI->form_validation->run())
        	{
        		return TRUE;
        	}
    	}
    	elseif ($table) // validate and insert data to database
    	{
    		if ($this->CI->form_validation->run())
    		{
    			$post_data = array();
    			foreach ($_POST as $key => $value)
    			{
    				$post_data[$key] = $value;
    			}
    			if (is_array($data)) 
    			{
    				foreach ($data as $key => $value)
    				{
    					if ($key != 'id')
    					{
    						$post_data[$key] = $value;
    					}
    				};
    			}
    			if (is_array($data) && isset($data['id']))
    			{
    				$this->CI->db->where('id', $data['id']);
    				$this->CI->db->update($table, $post_data);
    			}
    			else
    			{
    				$this->CI->db->insert($table, $post_data);
    			}

    			return TRUE;
    		}
    	}
    }
    
    function run_single($name, $name_default) 
    {
    	if ($name != $name_default)
    	{
    		$_POST[$name] = $_POST[$name_default];
    	}
    	if ($this->CI->form_validation->run())
    	{
    		return TRUE;
    	}
    }

    /**
     * Fetch error to display in view file
     */
    function fetch_error()
    {
        $error_count = 0;
        $error = '';

        foreach ($this->view_error as $value)
        {
            if ($value != NULL)
            {
                $error_count = $error_count + 1;
                $error .= $this->CI->config->item('el_err_top_1').$value.$this->CI->config->item('el_err_top_2');
            }
        }
        if ($error_count > 0)
        {
            if ($this->CI->config->item('error_position') != 1)
            {
                return $this->CI->config->item('el_err_top_1').'<b>Terdapat kesalahan dalam pengisian form</b>'.$this->CI->config->item('el_err_top_2');
            }      
            else if ($this->CI->config->item('error_position') == 1)
            {
                return $this->CI->config->item('el_err_top_above').$error.$this->CI->config->item('el_err_top_below');
            }            
        }
        else
        {
            return NULL;
        }
    }

    private function input_processing($name = '', $label = '', $rules = '') 
    {
       	$is_multi = FALSE;
    	$name_default = $name; // original name;
    	
    	// detecting multi name
    	if (substr_count($name, '[]'))
    	{
    		$is_multi = TRUE;
    		$name_subtr = substr($name, 0, -2);  // returns only name

    		// indexing name
    		$this->multi_input[$name_subtr][] = $name_subtr;
    		$m_i_count = count($this->multi_input[$name_subtr]); // multi input count
    		$end_key = $m_i_count - 1; // array key for last array
    		$this->multi_input[$name_subtr][$end_key] = $name_subtr.'_'.$end_key; // give indexing name;
    		$name = $this->multi_input[$name_subtr][$end_key];
    		
    		if (end($this->element_position) == 'multiselect') 
    		{
    			$name = $name_subtr.'['.$end_key.'][]';
    		}
    	}

        $this->get_rules($name, $label, $rules);
        
        // to handle add new multi name field
        if (! empty($_POST) && $is_multi)
        {
        	if (! empty($_POST[$name_subtr][$end_key]))
        	{
        		$_POST[$name] = $_POST[$name_subtr][$end_key];
        	} 
        	elseif (! isset($_POST[$name_subtr][$end_key])) {
        		$this->get_rules($name, $label, '');
        	}	
        }
        
        return $name; // Get new name if multi name is detected; 
    }
    
    private function set_empty_array($type, $name) 
    {
    	switch ($type) 
    	{
    		case 'input':
    		case 'dropdown':
    		case 'multiselect':
    		case 'upload':
    		case 'password':
    		case 'textarea':
    		case 'checkbox':
    		case 'radio':
    			$this->get_layout[] = '';
    			$this->element_name[] = $name;
    			$this->element_position[] = $type;
    			break;
    			
    		case 'open':
    		case 'open_multipart':
    		case 'close':
    		case 'fieldset':
    		case 'fieldset_close':
    		case 'hidden':
    		case 'label':
    		case 'submit':
    		case 'reset':
    		case 'br':
    		case 'hr':
    		case 'div':
    		case 'div_close':
    		case 'html':
    		case 'group_checkbox':
    		case 'group_radio':
    			$this->element_position[] = $type;
    			$this->element_name[] = $name;
    			$this->view_label[] = '';
    			$this->view_field[] = '';
    			$this->view_error[] = '';
    			$this->field_name[] = $name;
    			break;
    	};
    }
    
    /**
     * Convert attribute from array to string
     */
    private function get_attribute($attr)
    {
        $attrs = '';
        if (is_array($attr)) {
            foreach ($attr as $key => $value)
            {
                $attrs .= $key.'="'.$value.'" ';
            }
        } 
        else {
            $attrs = $attr;
        }
        return $attrs;
    }

    /**
     * Examines the rules using codeigniter form_validation library
     */
    private function get_rules($name, $label, $rules)
    {
        $this->CI->form_validation->set_rules($name, $label, $rules);
    }

    /**
     * Retrieve error message for an element
     */
    function get_error($name, $open_tag = '', $close_tag = '')
    {
        if (form_error($name))
        {
            return $open_tag.form_error($name).$close_tag;
        }
        return FALSE;
    }

    /**
     * Store the form layout and error message. Please see config file to edit style
     */
    private function get_layout($i, $special = '')
    {
        if ( ! $special)
        {
            if ($this->CI->config->item('error_position') == 1 OR $this->CI->config->item('error_position') == NULL)
            {
            	// Error messages are separated from the form
            	$this->get_layout[$i] =  $this->CI->config->item('el_1').$this->view_label[$i].$this->CI->config->item('el_2').
            		$this->view_field[$i].$this->CI->config->item('el_3');
            	return $this->get_layout[$i];
            }
            else if ($this->CI->config->item('error_position') == 3)
            {
                if ( ! empty($this->view_error[$i]))
                {
                	// Error message will be showed before label
                	$this->get_layout[$i] = $this->CI->config->item('el_err_bef_1').$this->view_error[$i].
                		$this->CI->config->item('el_err_bef_2').
                		$this->view_label[$i].$this->CI->config->item('el_err_bef_3').
                		$this->view_field[$i].$this->CI->config->item('el_err_bef_4');
                	return $this->get_layout[$i];
                }
                else
                {
                	// Layout if no error occur
                	$this->get_layout[$i] = $this->CI->config->item('el_1').$this->view_label[$i].$this->CI->config->item('el_2').
                	$this->view_field[$i].$this->CI->config->item('el_3');
                	return $this->get_layout[$i];
                }
            }
            else if ($this->CI->config->item('error_position') == 2)
            {
                if ( ! empty($this->view_error[$i]))
                {
                	// Error message will showed after field box
                	$this->get_layout[$i] = $this->CI->config->item('el_err_aft_1').$this->view_label[$i].
                		$this->CI->config->item('el_err_aft_2').
                		$this->view_field[$i].$this->CI->config->item('el_err_aft_3').
                		$this->view_error[$i].$this->CI->config->item('el_err_aft_4');
                	return $this->get_layout[$i];
                }
                else
                {
                	// Layout if no error occur
                	$el_ajx_first = '';
                	$el_ajx_middle = '';
                	$el_ajx_end = '';
                	if ($this->CI->config->item('el_ajx_1')) {
                		$el_ajx_first = $this->CI->config->item('el_ajx_1').' id = "'.$this->field_name[$i].'" style="display:none">'.$this->CI->config->item('el_ajx_1');
                	}
                	if ($this->CI->config->item('el_ajx_3')) {
                		$el_ajx_middle = $this->CI->config->item('el_ajx_3').' id = "'.$this->field_name[$i].'" style="display:none">'.$this->CI->config->item('el_ajx_4');
                	}
                	if ($this->CI->config->item('el_ajx_5')) {
                		$el_ajx_end = $this->CI->config->item('el_ajx_5').' id = "'.$this->field_name[$i].'" style="display:none">'.$this->CI->config->item('el_ajx_6');
                	}
                	
                	$this->get_layout[$i] = $this->CI->config->item('el_1').$el_ajx_first.$this->CI->config->item('el_2').$this->view_label[$i].
                		$this->CI->config->item('el_3').$el_ajx_middle.$this->CI->config->item('el_4').
                		$this->view_field[$i].$this->CI->config->item('el_5').$el_ajx_end.$this->CI->config->item('el_6');
                	return $this->get_layout[$i];
                }
            }
        }
        else if ($special == 'checkbox_radio')      // Checkbox and radio can be grouped so that we considered it as special threat
        {
            if ($this->CI->config->item('error_position') == 1 OR $this->CI->config->item('error_position') == NULL)
            {
            	// Error messages are separated from the form
            	$this->get_layout[$i] = $this->CI->config->item('el_sp_1').$this->view_field[$i].$this->CI->config->item('el_sp_2').
            		$this->view_label[$i].$this->CI->config->item('el_sp_3');
            	return $this->get_layout[$i];
            }
            else if ($this->CI->config->item('special_error_position') == 1)
            {
                if ( ! empty($this->view_error[$i]))
                {
                	// Error message will be placed after input box
                	$this->get_layout[$i] = $this->CI->config->item('el_sp_aft_1').$this->view_field[$i].$this->CI->config->item('el_sp_aft_2').
                		$this->view_label[$i].$this->CI->config->item('el_sp_aft_3').
                		$this->view_error[$i].$this->CI->config->item('el_sp_aft_4');
                	return $this->get_layout[$i];
                }
                else
                {
                	// Layout if no error occur
                	$this->get_layout[$i] = $this->CI->config->item('el_sp_1').$this->view_field[$i].$this->CI->config->item('el_sp_2').
                		$this->view_label[$i].$this->CI->config->item('el_sp_3');
                	return $this->get_layout[$i];
                }
            }
            else if ($this->CI->config->item('special_error_position') == 2)
            {
                if ( ! empty($this->view_error[$i]))
                {
                	// Error message will be placed before label
                	$this->get_layout[$i] = $this->CI->config->item('el_sp_bef_1').$this->view_error[$i].
                		$this->CI->config->item('el_sp_bef_2').
                		$this->view_field[$i].$this->CI->config->item('el_sp_bef_3').
                		$this->view_label[$i].$this->CI->config->item('el_sp_bef_4');
                	return $this->get_layout[$i];
                }
                else
                {
                	// Layout if no error occur
                	$this->get_layout[$i] = $this->CI->config->item('el_sp_1').$this->view_field[$i].$this->CI->config->item('el_sp_2').
                		$this->view_label[$i].$this->CI->config->item('el_sp_3');
                	return $this->get_layout[$i];
                }
            }
        }
    }

    /**
     * Store element field and error message as array
     */
    private function get_partial_view($type = '', $name = '', $label = '', $attr_field = '', $attr_label = '', $items = '', $bool = '', $default = '', $required = 0, $is_grouped = FALSE)
    {
        // Get every element error
        if ($this->get_error($name))
        {
            $this->view_error[] =  $this->get_error($name);
            $this->is_error[] = 'error';
            $attr_field['class'] = (isset($attr_field['class'])?$attr_field['class']:'').' '.$this->CI->config->item('field_error_class');
            $attr_label['class'] = (isset($attr_label['class'])?$attr_label['class']:'').' '.$this->CI->config->item('label_error_class');

            if ($is_grouped) 
            {
            	$attr_label['class']= '';
            	$num_layout = count($this->get_layout);
            	$num_group_label = $num_layout - 2;
				$old_label = $this->original_group[$num_group_label]['label'];
				$old_name = $this->original_group[$num_group_label]['name'];
				$attr_label_group = $this->original_group[$num_group_label]['attr_label'];
				$attr_label_group['class']= (isset($attr_label_group['class'])?$attr_label_group['class']:'').' '.$this->CI->config->item('label_error_class');

            	$this->get_layout[$num_group_label] = form_label($old_label, $old_name, $attr_label_group);
            }
        }
        else
        {
            $this->view_error[] = '';
        }
        switch ($type)
        {
            case 'input':
            	// default attribute
                $data_field = array (
                    'id'    => $name,
                    'name'  => end($this->element_name),
                    'value' => set_value($name, $default),
                );
                
                // append/replace with user input attribute
                if (isset($attr_field) AND is_array($attr_field))
                {
                    foreach ($attr_field as $key=>$value)
                    {
                        $data_field[$key] = $value;
                    }
                }
                $field = form_input($data_field);
                break;
            case 'textarea':
                $data_field = array (
                    'id'    => $name,
                    'name'  => end($this->element_name),
                    'value' => set_value($name, $default),
                );
                if (isset($attr_field) AND is_array($attr_field))
                {
                    foreach ($attr_field as $key=>$value)
                    {
                        $data_field[$key] = $value;
                    }
                }
                $field = form_textarea($data_field);
                break;                
            case 'dropdown':
            	// default attribute
            	$data_field = array (
                    'id'    => $name,
                );
                
                // append/replace with user input attribute
                if (isset($attr_field) AND is_array($attr_field))
                {
                    foreach ($attr_field as $key=>$value)
                    {
                        $data_field[$key] = $value;
                    }
                }
                $attr_field = $this->get_attribute($data_field);
                $field = form_dropdown(end($this->element_name), $items, set_value($name, $default), $attr_field);
                break;
            case 'multiselect':
              	$data_field = array (
                    'id'    => $name,
                );
                if (isset($attr_field) AND is_array($attr_field))
                {
                    foreach ($attr_field as $key=>$value)
                    {
                        $data_field[$key] = $value;
                    }
                }
                $attr_field = $this->get_attribute($data_field);
                $field = form_multiselect($name, $items, set_value($name, $default), $attr_field);
                break;
            case 'upload':
                $data_field = array (
                    'id'    => $name,
                    'name'  => $name,
                    'value' => '',
                );
                if (isset($attr_field) AND is_array($attr_field))
                {
                    foreach ($attr_field as $key=>$value)
                    {
                        $data_field[$key] = $value;
                    }
                }
                $field = form_upload($data_field);
                break;
            case 'password':
                $data_field = array (
                    'id'    => $name,
                    'name'  => $name,
                    'value' => set_value($name, $default),
                );
                if (isset($attr_field) AND is_array($attr_field))
                {
                    foreach ($attr_field as $key=>$value)
                    {
                        $data_field[$key] = $value;
                    }
                }
                $field = form_password($data_field);
                break;
            case 'checkbox':
                if ( ! empty($default))
                {
                	if (is_array($default))
                	{
                		$bool = (in_array($items, $default)) ? TRUE : FALSE;
                	}
                	else 
                	{
                		$bool = ($items == $default) ? TRUE : FALSE;
                	}
                }
                $field = form_checkbox($name, $items, set_checkbox($name, $items, $bool), $attr_field);
                break;
            case 'radio':
                if ( ! empty($default))
                {
                	if (is_array($default))
                	{
                		$bool = (in_array($items, $default)) ? TRUE : FALSE;
                	}
                	else
                	{
                		$bool = ($items == $default) ? TRUE : FALSE;
                	}
                }
                $field = form_radio($name, $items, set_radio($name, $items, $bool), $attr_field);
                break;
        }
        // Label preparation for required rules
        if ($label) 
        {
	        $label_del = $this->CI->config->item('label_delimiter');
	        $before_el = $this->CI->config->item('label_req_bef');
	        $after_el = $this->CI->config->item('label_req_aft');
	        
	        // Label attribut preparation. Use '_label' to be more specific with label selector
	        $data_label_field = array (
	            'id'    => $name.'_label',
	        );
	        if (isset($attr_label) AND is_array($attr_label))
	        {
	            foreach ($attr_label as $key=>$value)
	            {
	                $data_label_field[$key] = $value;
	            }
	        }
	        $attr_label = $data_label_field;
	        
	        if ($is_grouped)
	        { 	
	        	$this->view_label[] = form_label($label, $name, $attr_label);
	        }
	        elseif ($type == 'checkbox' OR $type == 'radio')
	        {
	            
	            if ($required > 0)
	            {
	                $this->view_label[] = form_label($before_el.$label.$after_el, $name, $attr_label);
	            }
	            else
	            {
	                $this->view_label[] = form_label($label, $name, $attr_label);
	            }
	        }
	        elseif ($required > 0)
	        {
	            $this->view_label[] = form_label($before_el.$label.$after_el.$label_del, $name, $attr_label);
	        }
	        else
	        {
	            $this->view_label[] = form_label($label.$label_del, $name, $attr_label);
	        }
        } else {
        	$this->view_label[] = '';
        }
        $this->view_field[] = $field;
        $this->field_name[] = $name;
    }


    /**
     * Get error value
     */
    private function get_validation()
    {
        $this->validation = validation_errors();
    }

    /**
     * Remove all array value. Usefull if we need multi form
     */
    function flush()
    {
        unset($this->label);
        unset($this->element_position);
        unset($this->element_name);
        unset($this->multi_input);
        unset($this->is_error);
        unset($this->hr);
        unset($this->br);
        unset($this->fieldset);
        unset($this->fieldset_close);
        unset($this->view_error);
        unset($this->view_field);
        unset($this->view_label);
        unset($this->hidden);
        unset($this->div);
        unset($this->div_close);
        unset($this->html);
    }

    /**
     * Remove all error value from array
     */
    function flush_error()
    {
        $this->is_error = array();
        $this->view_error = array();
    }

    /**
     * Gather all form strings
     */
    function get()
    {
    	$json_name_app = $this->CI->config->item('json_name_label_append');
    	$json_name_pre = $this->CI->config->item('json_name_label_prepend');
    	$json_val_app = $this->CI->config->item('json_value_label_append');
    	$json_val_pre = $this->CI->config->item('json_value_label_prepend');
    	
        // Default value
        $form = '';
        $this->json_error = '';

        // Default counter value
        $i = 0;     // For content counter

        foreach ($this->element_position as $value)
        {
            switch ($value)
            {
                case "upload":
                case "multiselect":
                case "input":
                case "textarea":
                case "password":
                case "dropdown":
                    $content = $this->get_layout($i);
                    $form .= $content;
                    $this->json_error .= ', "'.$json_name_pre.$this->field_name[$i].$json_name_app.'":"'.$json_val_pre.$this->view_error[$i].$json_val_app.'"';
                    //$this->json_error .= ', "'.$this->field_name[$i].'_error":"'.'<p class=\"error_note\">'.$this->view_error[$i].'</p>'.'"';
                    //$this->json_error .= ', "'.$this->field_name[$i].'_error":"'.$this->view_error[$i].'"';
                    $i++;
                    break;
                case "radio":
                case "checkbox":
                    $content = $this->get_layout($i, 'checkbox_radio');
                    $form .= $content;
                    if (! isset($this->is_grouped[$i]))
                    {
                        //$this->json_error .= ', "'.$this->field_name[$i].'_error":"'.$this->view_error[$i].'"';;
                    	$this->json_error .= ', "'.$json_name_pre.$this->field_name[$i].$json_name_app.'":"'.$json_val_pre.$this->view_error[$i].$json_val_app.'"';
                    	//$this->json_error .= ', "'.$this->field_name[$i].'_error":"'.'<p class=\"error_note\">'.$this->view_error[$i].'</p>'.'"';

                    }
                    $i++;
                    break;
                case "submit":
                case "button":
                case "fieldset":
                case "fieldset_close":
                case "br":
                case "hr":
                case "div":
                case "div_close":
                case "html":
                case "group_radio":
                case "group_checkbox":
                case "label":
                case "reset":
                case "open":
                case "close":
                case "open_multipart":
                case "hidden":
                    $content = $this->get_layout[$i];
                    $form .= $content;
                    $i++;
                    break;          
            }
        }
        return $form;
    }

    /**
    * Gather all form strings
    */
    function get_single_field()
    {
    	$json_name_app = $this->CI->config->item('json_name_label_append');
    	$json_name_pre = $this->CI->config->item('json_name_label_prepend');
    	$json_val_app = $this->CI->config->item('json_value_label_append');
    	$json_val_pre = $this->CI->config->item('json_value_label_prepend');
    	
    	// Default value
    	$form = array();
    	$this->json_error = '';
    
    	// Default counter value
    	$i = 0;     // For content counter
    
    	foreach ($this->element_position as $value)
    	{
    		switch ($value)
    		{
    			case "upload":
    			case "multiselect":
    			case "input":
    			case "textarea":
    			case "password":
    			case "dropdown":
    				$content = $this->get_layout($i);
    				$form[$this->field_name[$i]] = $content;
    				$this->json_error .= ', "'.$json_name_pre.$this->field_name[$i].$json_name_app.'":"'.$json_val_pre.$this->view_error[$i].$json_val_app.'"';
    				//$this->json_error .= ', "'.$this->field_name[$i].'_error":"'.$this->view_error[$i].'"';
    				$i++;
    				break;
    			case "radio":
    			case "checkbox":
    				$content = $this->get_layout($i, 'checkbox_radio');
    				$form[$this->field_name[$i]] = $content;
    				if (! isset($this->is_grouped[$i]))
    				{
    					//$this->json_error .= ', "'.$this->field_name[$i].'_error":"'.$this->view_error[$i].'"';;
    					$this->json_error .= ', "'.$json_name_pre.$this->field_name[$i].$json_name_app.'":"'.$json_val_pre.$this->view_error[$i].$json_val_app.'"';
    
    				}
    				$i++;
    				break;
    			case "submit":
    			case "button":
    			case "fieldset":
    			case "fieldset_close":
    			case "br":
    			case "hr":
    			case "div":
    			case "div_close":
    			case "html":
    			case "group_radio":
    			case "group_checkbox":
    			case "label":
    			case "reset":
    			case "open":
    			case "close":
    			case "open_multipart":
    			case "hidden":
    				$content = $this->get_layout[$i];
    				$form[$this->field_name[$i]] = $content;
    				$i++;
    				break;
    		}
    	}
    	return $form;
    }    
    
    function get_json()
    {
        return str_replace('[]', '', $this->json_error);
    }

    function get_field($name = '') 
    {
    	$key = array_search($name, $this->field_name);
    	return $this->view_field[$key];
    }
    
    function get_label($name = '') 
    {
    	$key = array_search($name, $this->field_name);
    	return $this->view_label[$key];
    }
        
    /**
     * Form open tag
     * 
     * @access	public
     * @param	string	url destination if form has been submitted
     * @param	array|string	attribute
     * @return	array
     */
    function open($action, $attr_open = '')
    {
    	$this->set_empty_array('open', 'open');
        $attr_open = $this->get_attribute($attr_open);
        $this->get_layout[] = form_open($action, $attr_open);
    }

    /**
     * Form multipart if using upload field
     * @param string        $action     url destination if form has been submitted
     * @param string|array  $attr_open  attribute
     */
    function open_multipart($action, $attr_open = '')
    {
    	$this->set_empty_array('open_multipart', 'open_multipart');
        $attr_open = $this->get_attribute($attr_open);
        $this->get_layout[] = form_open_multipart($action, $attr_open);
    }

    /**
     * Form close tag
     * @param	string	permits you to pass data to it which will be added below the tag  
     */
    function close($string = '')
    {
    	$this->set_empty_array('close', 'close');
        $this->get_layout[] = form_close($string);
    }
    
    /**
     * Form fieldset element
     */
    function fieldset($legend = '', $attr_field = '')
    {
    	$this->set_empty_array('fieldset', 'fieldset');
        $this->get_layout[] = form_fieldset($legend, $attr_field);
    }

    /**
     * Form fieldset close element
     */
    function fieldset_close($string = '')
    {
    	$this->set_empty_array('fieldset_close', 'fieldset_close');
        $this->get_layout[] = form_fieldset_close($string);
    }    


    /**
     * Form hidden element
     * @param string $name      hidden field's name
     * @param string $value     value of hidden field
     */
    function hidden($name, $value)
    {
    	$this->set_empty_array('hidden', $name);
        $this->get_rules($name, 'Dummy', 'required');
        $this->get_layout[] = form_hidden($name, $value);
    }

    /**
     * Form label element
     */
    function label($label = '', $name = '', $attr_label = '')
    {
    	$this->set_empty_array('label', $name);
        $this->get_layout[] = form_label($label, $name, $attr_label);
    }


    /**
     * Form submit element
     * @param string        $name
     * @param string        $label
     * @param string|array  $attr_reset
     */
    function submit($name, $label, $attr_submit = '')
    {
    	$this->set_empty_array('submit', $name);
        $attr_submit = $this->get_attribute($attr_submit);
        $this->get_layout[] = $this->CI->config->item('el_before_button').form_submit($name, $label, $attr_submit).$this->CI->config->item('el_after_button');
    }

    /**
     * Form button element
     * @param string        $name
     * @param string        $label
     * @param string  		$attr_button
     * @param string  		$attr_label
     */
    function button($name, $label, $attr_button = '', $attr_label = '')
    {
        $attr_button = $this->get_attribute($attr_button);
        $attr_label = $this->get_attribute($attr_label);
        $field = '<button type="submit" '.$attr_button.' name="'.$name.'">';
        $label = '<span '.$attr_label.'>'.$label.'</span></button>';
		$this->set_empty_array('submit', $name);
        $this->get_layout[] = $this->CI->config->item('el_before_button').$field.$label.$this->CI->config->item('el_after_button');
    }

    /**
     * Form reset element
     * @param string        $name
     * @param string        $label
     * @param string|array  $attr_reset
     */
    function reset($name, $label, $attr_reset = '')
    {
    	$this->set_empty_array('reset', $name);
        $attr_reset = $this->get_attribute($attr_reset);
        $this->get_layout[] = $this->CI->config->item('el_before_button').form_reset($name, $label, $attr_reset).$this->CI->config->item('el_after_button');
    }

    /**
     * HTML element br
     */
    function br()
    {
    	$this->set_empty_array('br', 'br');
        $this->get_layout[] = '<br>';
    }

    /**
     * HTML element hr
     */
    function hr()
    {
    	$this->set_empty_array('hr', 'hr');
        $this->get_layout[] = '<hr>';
    }

    /**
     * HTML div open / autoclose
     * @param string    $attr       can be filled with "id" or "class"
     * @param string    $val        value of "id" or "class"
     * @param bool      $autoclose  TRUE will make </div> tag after <div>
     */
    function div($attr = '', $val = '', $autoclose = FALSE, $content = '')
    {
    	$this->set_empty_array('div', 'div');
        if ($autoclose)
        {
            $this->get_layout[] = '<div '.$attr.'="'.$val.'">'.$content.'</div>';
        }
        else
        {
            $this->get_layout[] = '<div '.$attr.'="'.$val.'">';
        }
    }

    /**
     * HTML div close
     */
    function div_close()
    {
    	$this->set_empty_array('div_close', 'div_close');
        $this->get_layout[] = '</div>';
    }

    /**
     * HTML
     */
    function html($html)
    {
    	$this->set_empty_array('html', 'html');
        $this->get_layout[] = $html;  
    }
    
    /**
     * Form input element
     * @param	string	input name
     * @param	string	label name
     * @param	string	default value on input box
     * @param	string	rules
     * @param	array
     * @param	array
     */
    function input($name, $label, $default = '', $rules = '', $attr_input = '', $attr_label = '')
    {
    	// Warning don't change order  
        $required = substr_count($rules, 'required');
        $this->set_empty_array('input', $name);
        $name = $this->input_processing($name, $label, $rules);
        $this->run();
        $this->get_partial_view('input', $name, $label, $attr_input, $attr_label, '', '', $default, $required);
        
    }

    /**
     * Form textarea element
      * @param string       $name       field name
      * @param string       $label      label
      * @param array        $default    default value
      * @param string       $rules      rule
      * @param array|string $attr_input attribute for field
      * @param array        $attr_label attribute for label
     */
    function textarea($name, $label, $default = '', $rules = '', $attr_input = '', $attr_label = '')
    {
        $required = substr_count($rules, 'required');
        $this->set_empty_array('textarea', $name);
        $name = $this->input_processing($name, $label, $rules);
        $this->run();
        $this->get_partial_view('textarea', $name, $label, $attr_input, $attr_label, '', '', $default, $required);
    }
    
    /**
     * From dropdown element
     */
    function dropdown($name, $label, $items = '', $default = '', $rules = '', $attr_input = '', $attr_label = '')
    {
        $required = substr_count($rules, 'required');
		$this->set_empty_array('dropdown', $name);
        $name = $this->input_processing($name, $label, $rules);
        $this->run();        
        $this->get_partial_view('dropdown', $name, $label, $attr_input, $attr_label, $items, '', $default, $required);
        
    }
    
    /**
     * Form multiselect element
     */
    function multiselect($name, $label, $items = '', $default = '', $rules = '', $attr_input = '', $attr_label = '')
    {
    	$required = substr_count($rules, 'required');
        $this->set_empty_array('multiselect', $name);
        $name = $this->input_processing($name, $label, $rules);
        $this->run();
        $this->get_partial_view('multiselect', $name, $label, $attr_input, $attr_label, $items, '', $default, $required);          
    }
  
    /**
     * Form password element
     * @param string       $name       field name
     * @param string       $label      label
     * @param array        $default    default value
     * @param string       $rules      rule
     * @param array|string $attr_input attribute for field
     * @param array        $attr_label attribute for label
     */
    function password($name, $label, $default = '', $rules = '', $attr_input = '', $attr_label = '')
    {        
    	$required = substr_count($rules, 'required');
        $this->set_empty_array('password', $name);
        $this->get_rules($name, $label, $rules);
        $this->run();
        $this->get_partial_view('password', $name, $label, $attr_input, $attr_label, '', '', $default, $required);
    }

    /**
     * This handle on upload request
     * @param string           $name           upload name
     * @param string           $label          upload label
     * @param string           $rules          rule
     * @param array|string     $attr_input     attribute for text field
     * @param array            $attr_label     attribute for label
     */
    function upload($name, $label, $rules = '', $attr_input = '', $attr_label = '')
    {
    	$required = substr_count($rules, 'required');
    	$this->set_empty_array('upload', $name);
        $current_position = count($this->element_position) - 1;
        $this->upload_is_required[$current_position] = FALSE;
        if ($required)
        {
            $this->upload_is_required[$current_position] = TRUE;
        }
        $this->get_partial_view('upload', $name, $label, $attr_input, $attr_label, '', '', '', $required);
    }

    /**
     * Form label for checkboxes group
     * @param string   $name       checkbox name
     * @param string   $label      group label
     * @param array    $items      configuration for each checkbox in this group
     * @param array    $default    default checkbox value
     * @param string   $rules      rule
     * @param array    $attr_label attribute for group's label
     */
    function group_checkbox($name = '', $label = '', $items = '', $default = '', $rules = '', $attr_label = '')
    {
        $required = substr_count($rules, 'required');
        $this->set_empty_array('group_checkbox', $name);
        $label_group = $label;

        // Label preparation for required rules
        $label_del = $this->CI->config->item('label_delimiter');
        $before_el = $this->CI->config->item('label_req_bef');
        $after_el = $this->CI->config->item('label_req_aft');

        if ($required > 0)
        {
            $this->get_layout[] = form_label($before_el.$label.$after_el.$label_del, $name, $attr_label);
            $count_layout = count($this->get_layout);
            $current_array_num = $count_layout - 1; 
            $this->original_group[$current_array_num]['label'] = $before_el.$label.$after_el.$label_del;
        }
        else
        {
            $this->get_layout[] = form_label($label.$label_del, $name, $attr_label);
            $count_layout = count($this->get_layout);
            $current_array_num = $count_layout - 1; 
            $this->original_group[$current_array_num]['label'] = $label.$label_del;
        }
        $this->original_group[$current_array_num]['name'] = $name;
        $this->original_group[$current_array_num]['attr_label'] = $attr_label;
        
        // Preparation checkbox item
        $i = 0;
        foreach ($items as $key => $values)
        {
            $items = $key;
            $attr_input = '';
            $attr_label = '';
            $bool = FALSE;
            $required = 0;
            if (is_array($values))
            {
                $label = $values[0];
                $attr_input = $this->get_attribute($values[1]);
                $attr_label = $values[2];
                $default = $values[3];
            }   
            else
            {
                $label = $values;
            }

            if ($i == 0)
            {
                $this->checkbox($name, $label, $label_group, $items, $default, $rules, '', $attr_input, $attr_label, $required, TRUE);
            }
            else
            {
                //$group_position = count($this->element_position);
                //$this->is_grouped[$group_position] = TRUE;
                $this->checkbox($name, $label, '', $items, $default, '', '', $attr_input, $attr_label, $required, TRUE);
            }
            $i++;
        }
    }

    /**
     * Form label for radio group
     * @param string   $name       radio name
     * @param string   $label      group label
     * @param array    $items      configuration for each radio in this group
     * @param array    $default    default radio value
     * @param string   $rules      rule
     * @param array    $attr_label attribute for group's label
     */
    function group_radio($name = '', $label = '', $items = '', $default = '', $rules = '', $attr_label = '')
    {
        $required = substr_count($rules, 'required');
        $this->set_empty_array('group_radio', $name);
        $label_group = $label;

        // Label preparation for required rules
        $label_del = $this->CI->config->item('label_delimiter');
        $before_el = $this->CI->config->item('label_req_bef');
        $after_el = $this->CI->config->item('label_req_aft');

        if ($required > 0)
        {
            $this->get_layout[] = form_label($before_el.$label.$after_el.$label_del, $name, $attr_label);
            $count_layout = count($this->get_layout);
            $current_array_num = $count_layout - 1; 
            $this->original_group[$current_array_num]['label'] = $before_el.$label.$after_el.$label_del;
            $this->original_group[$current_array_num]['name'] = $name;
            $this->original_group[$current_array_num]['attr_label'] = $attr_label;	
        }
        else
        {
            $this->get_layout[] = form_label($label.$label_del, $name, $attr_label);
            $count_layout = count($this->get_layout);
            $current_array_num = $count_layout - 1; 
            $this->original_group[$current_array_num]['label'] = $before_el.$label.$after_el.$label_del;
            $this->original_group[$current_array_num]['name'] = $name;
            $this->original_group[$current_array_num]['attr_label'] = $attr_label;	
        }

        // Preparation radio item
        $i = 0;
        foreach ($items as $key => $values)
        {
            $items = $key;
            $attr_input = '';
            $attr_label = '';
            $bool = FALSE;
            $required = 0;
            if (is_array($values))
            {
                $label = $values[0];
                $attr_input = $this->get_attribute($values[1]);
                $attr_label = $values[2];
                $default = $values[3];
            }
            else
            {
                $label = $values;
            }
            if ($i == 0)
            {
                $this->radio($name, $label, $label_group, $items, $default, $rules, '', $attr_input, $attr_label, $required, TRUE);
            }
            else
            {
                //$group_position = count($this->element_position);
                //$this->is_grouped[$group_position] = TRUE;
                $this->radio($name, $label, '', $items, $default, '', '', $attr_input, $attr_label, $required, TRUE);
            }
            $i++;
        }
    }

    /**
     * Form checkbox element
     * @param string        $name           field name
     * @param string        $label          label next to checkbox
     * @param string        $label_error    label that represent the checkbox field
     * @param string        $value          checkbox value
     * @param string        $default        default value, usually we store database value for this field in here
     * @param string        $rules          rule
     * @param bool          $checked        TRUE if lets you set an item as the default
     * @param string|array  $attr_input     attribute for checkbox
     * @param array         $attr_label     attribute for checkbox's label
     * @param bool          $skip_required  ignore required value from $rules. ** IGNORE this value! **
     */
    function checkbox($name, $label, $label_error = '', $value = '', $default = '', $rules = '', $checked = FALSE, $attr_input = '', $attr_label = '', $required = FALSE, $is_grouped = FALSE)
    {
    	$this->set_empty_array('checkbox', $name);

        if ($label_error)
        {
            $label_rules = $label_error;
        }
        else
        {
            $label_rules = $label;
        }
        $this->get_rules($name, $label_rules, $rules);
        $attr_input = $this->get_attribute($attr_input);
        $this->run();
//        if ($skip_required)
//        {
//            $required = '';
//        }
//        else
//        {
//            $required = substr_count($rules, 'required');
//        }
        $this->get_partial_view('checkbox', $name, $label, $attr_input, $attr_label, $value, $checked, $default, $required, $is_grouped);
    }

    /**
     * Form radio element
     * @param string        $name           field name
     * @param string        $label label    next to radio button
     * @param string        $label_error    label that represent the radio field
     * @param string        $value          radio value
     * @param string        $default        default value, usually we store database value for this field in here
     * @param string        $rules          rule
     * @param bool          $checked        TRUE if lets you set an item as the default
     * @param string|array  $attr_input     attribute for radio's button
     * @param array         $attr_label     attribute for radio's label
     * @param bool          $skip_required  ignore required value from $rules. IGNORE this value!
     */
    function radio($name, $label, $label_error = '', $value = '', $default = '', $rules = '', $checked = FALSE, $attr_input = '', $attr_label = '', $required = FALSE, $is_grouped = FALSE)
    {
    	$this->set_empty_array('radio', $name);

        if ($label_error)
        {
            $label_rules = $label_error;
        }
        else
        {
            $label_rules = $label;
        }
        $this->get_rules($name, $label_rules, $rules);
        $attr_input = $this->get_attribute($attr_input);
        $this->run();
//        if ($skip_required)
//        {
//            $required = '';
//        }
//        else
//        {
//            $required = substr_count($rules, 'required');
//        }
        $this->get_partial_view('radio', $name, $label, $attr_input, $attr_label, $value, $checked, $default, $required, $is_grouped);
    }

    /**
     * Check whether upload field empty or not and need to check or not. TRUE if not empty and need to check, otherwise FALSE
     * @param   string $name            upload field name
     * @param   string $upload_key      upload key from $this->field_name array
     * @return  bool                    TRUE if not empty and need to check
     */
    function check_upload($name, $upload_key = '')
    {
        log_message('error', 'Enter FUNCTION CHECK_upload !!');
        if (! $upload_key)
        {
            $upload_key = array_search($name, $this->field_name);
        }
        // First time
        if ($this->first_load == TRUE)
        {
            log_message('error', 'CHECK_upload 1');
            return FALSE;
        }
        else if ($this->upload_is_required[$upload_key] == TRUE && empty($_FILES[$name]['name']))
        {
            log_message('error', 'CHECK_upload 2');
            return TRUE;
        }
        else if (empty($_FILES[$name]['name']))
        {
            log_message('error', 'CHECK_upload 3');
            return FALSE;
        }
        else
        {
            log_message('error', 'CHECK_upload 4');
            return TRUE;
        }

    }

    /**
     * Get the file to upload if meet the criteria provided
     * @param string $name          upload field name
     * @param string $upload_key    upload key from $this->field_name array
     */
    function get_upload($name, $upload_key)
    {
        log_message('error', 'MASUK GET_upload');
        if ($this->first_load == FALSE && $this->upload_is_required[$upload_key] == TRUE)
        {
            log_message('error', 'GET_upload 1');
            $this->CI->upload->do_upload($name);
        }
        elseif ($this->upload_is_required[$upload_key] == FALSE && $this->check_upload($name, $upload_key))
        {
            log_message('error', 'GET_upload 2');
            $this->CI->upload->do_upload($name);
        }
    }

    /**
     * Utilize CI do_upload that take action after other validation is pass
     * @param   string  $name       upload field name
     * @param   array   $config     configuration of upload
     * @return  bool
     */
    function do_upload($name, $config = '')
    {
        // For security reasons
        if ( ! $config)
        {
            $config['max_size'] = '1';
            $this->CI->load->library('upload', $config);
        }
        // The upload better do after all other validation pass
        $upload_key = array_search($name, $this->field_name);
        $sum_error = 0;
        foreach ($this->view_error as $value)
        {
            if ($value == '')
            {
                $num_error = 0;
            }
            else
            {
                $num_error = 1;
            }
            $sum_error = $sum_error + $num_error;
        }
        if ($sum_error == 0)
        {
            $this->get_upload($name, $upload_key);
            // Display error
             if ($this->CI->upload->display_errors())
             {
                 $this->view_error[$upload_key] =  $this->CI->upload->display_errors('', '');
                 $this->is_error[$upload_key] = 'error';
                 $this->is_upload = FALSE;
             }
             else
             {
                 $this->is_upload = TRUE;
             }
        }

        if ($this->CI->upload->display_errors())
        {
            log_message('error', 'MASUK KE 1');
            return FALSE;
        }
        else if ($this->upload_is_required[$upload_key] == TRUE && $this->check_upload($name, $upload_key))
        {
            log_message('error', 'MASUK KE 2');
            return TRUE;
        }
        else if ($this->upload_is_required[$upload_key] == FALSE && $this->check_upload($name, $upload_key))
        {
            log_message('error', 'MASUK KE 3');
            return TRUE;
        }
        else if ($this->upload_is_required[$upload_key] == FALSE && ! $this->check_upload($name, $upload_key))
        {
            log_message('error', 'MASUK KE 4');
            return FALSE;
        }
        else if ($this->upload_is_required[$upload_key] == TRUE && ! $this->check_upload($name, $upload_key))
        {
            log_message('error', 'MASUK KE 5');
            return FALSE;
        }
    }

    /**
     * Utilize resize function from Image Manipulation class
     * @param   string  $name       upload field name
     * @param   array   $config     configuration of image lib
     * @return  bool
     */
    function image_resize($name, $config = '')
    {
        if ($this->is_upload == TRUE)
        {
            $this->CI->load->library('image_lib', $config);
            $this->CI->image_lib->resize();
            if ($this->CI->image_lib->display_errors())
            {
                $upload_key = array_search($name, $this->field_name);
                $this->view_error[$upload_key] =  $this->CI->image_lib->display_errors('', '');
                $this->is_error[$upload_key] = 'error';
                return FALSE;
            }
            else
            {
                return TRUE;
            }
        }
    }

    /**
     * Utilize crop function from Image Manipulation class
     */
    function image_crop($name, $config = '')
    {
        if ($this->is_upload == TRUE)
        {
            $this->CI->load->library('image_lib', $config);
            $this->CI->image_lib->crop();
            if ($this->CI->image_lib->display_errors())
            {
                $upload_key = array_search($name, $this->field_name);
                $this->view_error[$upload_key] =  $this->CI->image_lib->display_errors('', '');
                $this->is_error[$upload_key] = 'error';
                return FALSE;
            }
            else
            {
                return TRUE;
            }
        }
    }
    
    /**
     * Utilize rotate function from Image Manipulation class
     */
    function image_rotate($name, $config = '')
    {
        if ($this->is_upload == TRUE)
        {
            $this->CI->load->library('image_lib', $config);
            $this->CI->image_lib->rotate();
            if ($this->CI->image_lib->display_errors())
            {
                $upload_key = array_search($name, $this->field_name);
                $this->view_error[$upload_key] =  $this->CI->image_lib->display_errors('', '');
                $this->is_error[$upload_key] = 'error';
                return FALSE;
            }
            else
            {
                return TRUE;
            }
        }
    }
    
    /**
     * Utilize watermark function from Image Manipulation class
     */
    function image_watermark($name, $config = '')
    {
        if ($this->is_upload == TRUE)
        {
            $this->CI->load->library('image_lib', $config);
            $this->CI->image_lib->watermark();
            if ($this->CI->image_lib->display_errors())
            {
                $upload_key = array_search($name, $this->field_name);
                $this->view_error[$upload_key] =  $this->CI->image_lib->display_errors('', '');
                $this->is_error[$upload_key] = 'error';
                return FALSE;
            }
            else
            {
                return TRUE;
            }
        }
    }
}

/* End of file Autoform.php */
/* Location: ./application/libraries/Autoform.php */