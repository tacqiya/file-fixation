<?php
if (!isset($_SESSION)) {
    session_start();
}

class Main_model extends CI_Model
{

    public function __construct()
    {
        $this->load->database();
    }

    public function register_data($table, $data)
    {
        $this->db->insert($table, $data);
        return ($this->db->trans_status()) ? $this->db->insert_id() : false;
    }

    public function getRowBy($table, $by, $value, $limit = null, $offset = null)
    {
        $query = $this->db->get_where($table, array($by => $value), $limit, $offset);
        if ($query->num_rows() > 0) {
            return $query->row();
        } else {
            return FALSE;
        }
    }

    public function getAllByMulty($table, $array, $order_by, $order, $limit = null, $offset = null)
    {
        $query = $this->db->order_by($order_by, $order)->get_where($table, $array, $limit, $offset);
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }

    public function fetchAllDetails($table, $column)
    {
        $this->db->select('*');
        $this->db->where($column);
        $q = $this->db->get($table);
        $response = $q->row();
        return $response;
    }

    public function getLast($table)
    {
        $this->db->limit(1);
        $this->db->order_by('id', 'desc');
        $query = $this->db->get($table);
        return $query->result_array();
    }

    public function updateQuery($table, $whereClauseArray, $valueArray, $whereInClauseArray = null)
    {
        if (!is_null($whereClauseArray)) {
            $this->db->where($whereClauseArray);
        }
        if (!is_null($whereInClauseArray)) {
            $this->db->where_in($whereInClauseArray['column'], $whereInClauseArray['values']);
        }
        $this->db->update($table, $valueArray);
        return ($this->db->trans_status()) ? true : false;
    }

    public function updateAllQuery($table, $columnName, $valueArray, $whereClauseArray = null)
    {
        if (!is_null($whereClauseArray)) {
            $this->db->where($whereClauseArray);
        }
        $this->db->update_batch($table, $valueArray, $columnName);
        return ($this->db->trans_status()) ? true : false;
    }

    public function getAll($table, $order_by, $order, $limit = null, $offset = null)
    {
        $query = $this->db->order_by($order_by, $order)->get($table, $limit, $offset);
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }
}
