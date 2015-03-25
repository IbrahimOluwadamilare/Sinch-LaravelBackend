<?php namespace App\Http\Models;

use App\Http\Models\Base as Model;

class User extends Model {

    private $_col   = "users";

    public function find( $where ) {
        return $this->_findOne( $this->_col, $where );
    }

    public function create( $user ) {
        return $this->_insert( $this->_col, $user );
    }
}