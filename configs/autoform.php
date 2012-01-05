<?php

/**
 * By default Codeigniter will wrap any error with <p> tags.
 * Here, you can alter default setting.
 * Example
 * $config['ci_global_error_wrap'] = '<p>|</p>';
 * 
 * Default:
 * $config['ci_global_error_wrap'] = '|';
 *
 *
 */
$config['ci_global_error_wrap'] = '|';

$config['label_error_class'] = 'error';
$config['field_error_class'] = 'error';


/**
 * This will add a character after label
 * default $config['label_delimiter'] = ' :';
 */
$config['label_delimiter'] = ' : ';


/**
 * Set style for label that required to filled
 *
 * {label_req_bef}{LABEL}{label_req_aft}
 */
$config['label_req_bef'] = '';
$config['label_req_aft'] = ' <em>*</em>';


/**
 * JSON Configurator
 * 
 * JSON data will be :
 * "{json_name_label_prepend}field-has-error{json_name_label_append}":"{json_value_label_prepend}error-message{json_value_label_append}"
 * 
 */
$config['json_name_label_prepend'] = '';
$config['json_name_label_append'] = '';

$config['json_value_label_prepend'] = '<p class=\"error_note\">';
$config['json_value_label_append'] = '</p>';


/**
 * ---------------------------------------
 * Normal error position
 *----------------------------------------
 * Normal error position options are:
 *      [1] = free position => error message will be placed wherever you put <?php echo $error; ?> in view file
 *      [2] = inline after => error message will be placed after input box
 *      [3] = inline before => error message will be placed before label box
 */
$config['error_position'] = 2;


/**
 * Set elements for No error showed
 * Can be used for ajax message container
 *
 * {el_1}{el_ajx_1}{el_ajx_2}{el_2}<label>...</label>{el_3}{el_ajx_3}{el_ajx_4}{el_4}<input type .... />{el_5}{el_ajx_5}{el_ajx_6}{el_6}
 *
 */
$config['el_ajx_1'] = ''; //without closing bracket because we need to store id base on form input
$config['el_ajx_2'] = '';
$config['el_ajx_3'] = ''; //without closing bracket because we need to store id base on form input
$config['el_ajx_4'] = '';
$config['el_ajx_5'] = '<div class="inline-error-ajax"'; //without closing bracket because we need to store id base on form input
$config['el_ajx_6'] = '</div>';

$config['el_1'] = '';
$config['el_2'] = '';
$config['el_3'] = '';
$config['el_4'] = '';
$config['el_5'] = '';
$config['el_6'] = '';

/**
 * Set elements for Error Position 1
 * Error messages will be placed wherever you put <?php echo $error; ?>
 *
 * {el_error_top_above}
 * {el_err_top_1}{error messages 1}{el_err_top_2}
 * {el_err_top_1}{error messages 2}{el_err_top_2}
 * {el_err_top_1}{error messages ...}{el_err_top_2}
 * {el_error_top_below}
 * {Your form content}
 *
 */
$config['el_error_top_above'] = '';
$config['el_err_top_1'] = '<div class="form_error">';
$config['el_err_top_2'] = '</div>';
$config['el_error_top_below'] = '';


/**
 * Set elements for Error Position 2
 * Error message will show up after field box
 *
 * {el_err_aft_1}<label>...</label>{el_err_aft_2}<input type .... />{el_err_aft_3}{error messages}{el_err_aft_4}
 *
 */
$config['el_err_aft_1'] = '';
$config['el_err_aft_2'] = '';
$config['el_err_aft_3'] = '<p class="error_note">';
$config['el_err_aft_4'] = '</p>';


/**
 * Set elements for Error Position 3
 * Error message will show up before label box
 *
 * {el_err_bef_1}{error messages}{el_err_bef_2}<label>...</label>{el_err_bef_3}<input type .... />{el_err_bef_4}
 *
 */
$config['el_err_bef_1'] = '<li><p class="error_note">';
$config['el_err_bef_2'] = '</p>';
$config['el_err_bef_3'] = '';
$config['el_err_bef_4'] = '</li>';


/**
 * Set elements for button
 *
 * {el_before_button}{<button or submit element}{el_after_button}
 *
 */
$config['el_before_button'] = '<div class="buttons">';
$config['el_after_button'] = '</div>';


/**
 * ----------------------------------
 * Special error position
 * ----------------------------------
 * 
 * Special error position are for checkbox and radio element and only affects if Normal error
 * position set to 2 or 3. Please see line 8 above (Normal Position Error).
 *
 * Error position options are:
 *      [1] = inline after => error message will be placed after input box => important: not suitable for most user!
 *      [2] = inline before => error message will be placed before label => recommended !
 */
$config['special_error_position'] = 2;


/**
 * No error showed
 *
 * {el_sp_1}<input type .... />{el_sp_2}<label>...</label>{el_sp_3}
 */
$config['el_sp_1'] = '<li class="label_checkbox_pair" style="clear:both">';
$config['el_sp_2'] = '';
$config['el_sp_3'] = '</li>';


/**
 * Set elements for Error Position 1
 * Error message will be placed after input box
 *
 * {el_sp_aft_1}<label>...</label>{el_sp_aft_2}<input type .... />{el_sp_aft_3}{error messages}{el_sp_aft_4}
 * @depreciated
 */
$config['el_sp_aft_1'] = '<p class="label_checkbox_pair">';
$config['el_sp_aft_2'] = '';
$config['el_sp_aft_3'] = '</p><p class="error_note">';
$config['el_sp_aft_4'] = '</p>';


/**
 * Set elements for Error Position 2
 * Error message will be placed before label
 *
 * {el_sp_bef_1}{error messages}{el_sp_bef_2}<input type .... />{el_sp_bef_3}<label>...</label>{el_sp_bef_4}
 */
$config['el_sp_bef_1'] = '<p class="error_note" style="clear: both">';
$config['el_sp_bef_2'] = '</p><li class="label_checkbox_pair" style="clear:both">';
$config['el_sp_bef_3'] = '';
$config['el_sp_bef_4'] = '</li>';

/* End of file autoform.php */
/* Location: ./application/config/autoform.php */