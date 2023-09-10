<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Welcome extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('session');
		$this->load->helper('cookie');
		$this->site_config = $this->config->item('site_config');
	}

	public function index()
	{
		$this->load->view('welcome_message');
	}

	public function upload_file_cf7_external_extension()
	{
		extract($this->input->post());
		$form_data = $this->input->post();
		$cf7_data = $this->dbconnect->getLast(TBL_CF7_DATA);
		$cf7_entry_data = $this->dbconnect->getAllByMulty(TBL_CF7_ENTRY, ['data_id' => ($cf7_data[0]['id']), 'cf7_id' => $_wpcf7], 'id', 'ASC');

		$temp_file_location = $_SERVER['DOCUMENT_ROOT'] . '/wordpress-test/file-fixation/file-storager/';
		$acf_file_path = $_SERVER['DOCUMENT_ROOT'] . '/wordpress-test/wp-content/uploads/advanced-cf7-upload/';
		$uploaddir = $_SERVER['DOCUMENT_ROOT'] . '/wordpress-test/wp-content/uploads/advanced-cf7-upload/';
		// ONLINE
		// $temp_file_location = base_url().'file-storager/';
		// $acf_file_path = 'https://www.ku.ac.ae/wp-content/uploads/advanced-cf7-upload/';
		// $uploaddir = 'https://www.ku.ac.ae/wp-content/uploads/advanced-cf7-upload/';

		$new_paths = [];
		$config['upload_path'] = './file-storager';
		$config['allowed_types'] = '*';
		$config['overwrite'] = TRUE;
		// $config['encrypt_name'] = TRUE;
		$config['max_size'] = 20000;
		//echo "<pre>"; print_r(count($_FILES));exit;
		foreach ($_FILES as $key => $file) {
			foreach ($cf7_entry_data as $entry) {
				if ($entry->name == $key) {
					$entry->type = 'file';
				}
			}
		}
		// echo "<pre>"; print_r($cf7_entry_data);
		//UPLOADING TO CODEIGNITER DIRECTORY
		foreach ($_FILES as $key => $file) {
			$file_info = pathinfo($file['name']); //echo "<pre>"; print_r($file_info);
			if ($file_info['filename']) {
				if (file_exists('./file-storager/' . $file['name'])) {
					$counter = 1;
					while (file_exists('./file-storager/' . $file['name'])) {
						$file['name'] = $file_info['filename'] . '-' . $counter . '.' . $file_info['extension'];
						$config['file_name'] = $file['name'];
						$counter++;
					};
				}

				$this->load->library('upload', $config);
				$this->upload->initialize($config);

				// echo "<pre>"; print_r($file); exit;
				if (!$this->upload->do_upload($key)) {
					$error = array('error' => $this->upload->display_errors());
					//echo json_encode(['status' => 'error', '']);
				} else {
					$data = array('upload_data' => $this->upload->data());
					//echo "uploaded";
				}

				if (file_exists('./file-storager/' . $file['name'])) {
					$new_paths[$key] = $temp_file_location . $file['name'];
				}
			}

			// array_push($new_paths, $temp_file_location . $file['name']);

			// sleep(20);
			//MOVING TO ADVANCED CF7 DIRECTORY
			// if (file_exists($uploaddir . $file['name'])) {
			// 	for ($i = 1; $i <= 20; $i++) {
			// 		$inner_file_path = $acf_file_path . $file_info['filename'] . '-' . $i . '.' . $file_info['extension'];
			// 		if (!file_exists($inner_file_path)) {
			// 			$previous_file = $acf_file_path . $file_info['filename'] . '-' . ($i - 1) . '.' . $file_info['extension'];
			// 			$file_name = $file_info['filename'] . '-' . $i . '.' . $file_info['extension'];
			// 			$j = $i - 1;
			// 			if ($j == 0) {
			// 				$file_name_prev = $file_info['filename'] . '.' . $file_info['extension'];
			// 			} else {
			// 				$file_name_prev = $file_info['filename'] . '-' . $j . '.' . $file_info['extension'];
			// 			}
			// 			// print_r($file_name); print_r($file_name_prev);exit;
			// 			if (date("F d Y H:i.", filemtime($previous_file)) != date("F d Y H:i.", filemtime($uploaddir . $file_name))) {
			// 				// copy($temp_file_location, $uploaddir . $file_name);
			// 				array_push($new_paths, $uploaddir . $file_name);
			// 				copy($temp_file_location . $file_name, $acf_file_path . $file_name);
			// 			} else {
			// 				// copy($temp_file_location, $uploaddir . $file_name_prev);
			// 				array_push($new_paths, $uploaddir . $file_name_prev);
			// 				// copy($temp_file_location . $file_name_prev, $acf_file_path . $file_name_prev);
			// 			}
			// 			break;
			// 		}
			// 	}
			// } else {
			// 	// copy($temp_file_location, $uploaddir . $file['name']);
			// 	array_push($new_paths, $uploaddir . $file['name']);
			// 	copy($temp_file_location . $file['name'], $acf_file_path . $file['name'] );
			// }
		}
		//INSERT TO DB
		if (count($new_paths) > 0) {
			$update_data = array(
				'form' => $form_data['_wpcf7'],
				'data' =>  $cf7_data[0]['id'],
				'file_data' => serialize($cf7_entry_data),
				'new_path' => serialize($new_paths),
				'date' => date('Y-m-d H:i:s')
			);
			$insert_data = $this->dbconnect->register_data(TBL_FILE_DATA, $update_data);

			echo json_encode(['status' => 'success', 'data' => $new_paths]);
		} else {
			echo json_encode(['status' => 'error']);
		}
	}
}
