<?php if (!defined('FW')) die('Forbidden');

interface Population_Method_Interface
{
	public function get_multimedia_types();
	public function get_population_method();
	public function get_number_of_images($post_id);
	public function get_population_options($multimedia_types, $custom_options);
	public function get_frontend_data($post_id);

}