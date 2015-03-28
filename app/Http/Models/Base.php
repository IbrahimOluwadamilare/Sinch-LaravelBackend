<?php namespace App\Http\Models;

use Illuminate\Support\Facades\Config;

class Base {

    private $_db            = null;

    private $_config        = null;

    private $_conn          = null;

    private $_data          = array();

    private $_ws            = array();

    private $_sls           = array();

    private $_st            = array();

    private $_from          = "";

    private $_lmt           = 99999;

    private $_ost           = 0;

    public function __construct() {
        $this->_config  = Config::get( 'mongodb' );

        // Connect to the MongoDB server
        $this->_connect();
    }

    private function _connect() {
        // Check the required configuration fields
        if( empty( $this->_config['host'] ) ) {

        }

        // Set the connection string
        $conn = 'mongodb://'.$this->_config['host'];
        // Check that a port is specified
        if( ! empty( $this->_config['port'] ) ) {
            $conn .= ":{$this->_config['port']}";
        }

        // Check that a database is specified to use instead of admin
        if( ! empty( $this->_config['db'] ) ) {
            $conn .= "/{$this->_config['db']}";
        }
        // Check if username/password is beign used
        $options = array();
        if( ! empty( $this->_config['user'] ) && ! empty( $this->_config['pass'] ) ) {
            // Use the options method instead of passing the user and pass in the address directly
            // useful for the case the user includes ":" or "@" on the pass
            $options['username'] = $this->_config['user'];
            $options['password'] = $this->_config['pass'];
        }

        // Connect to the database
        try {
            // Establish the connection to the server
            $this->_conn    = new \MongoClient( $conn, $options );

            // Connect to the required database
            $this->_db      = $this->_conn->{$this->_config['db']};
            return true;
        } catch( \MongoConnectionException $e ) {
            $this->_conn    = null;
            $this->_db      = null;
            abort( 500, "Mongo connection failed" );
            return false;
        }
    }

    protected function _count( $collection = "" ) {
        return $this->_db->{$collection}->count();
    }

    protected function _close() {
        $this->_db  = null;
        return $this->_conn->close();
    }

    protected function _find( $collection = "" ) {
        if ( empty( $collection ) ) {
            $collection = $this->_from;
        }

        $docs = $this->_db->{$collection}
            ->find( $this->_ws, $this->_sls )
            ->limit( $this->_lmt )
            ->skip( $this->_ost )
            ->sort( $this->_st );
        $this->_flush();

        $result = array();
        foreach( $docs as $row ) {
            $this->_deref( $row );
            $result[] = ( object ) $row;
        }
        return $result;
    }

    protected function _findOne( $collection = "", $where = array() ) {
        if ( empty( $collection ) ) {
            $collection = $this->_from;
        }

        $this->_set_where( $where );

        $user   = $this->_db->{$collection}->findOne( $this->_ws, $this->_sls );
        $this->_flush();
        return ( object ) $user;
    }

    protected function _from( $collection = "" ) {
        $this->_from    = $collection;
    }

    protected function _insert( $collection = "", $data = array(), $options = array(), $batch = false ) {
        if( empty( $collection ) ) {
            $collection = $this->_from;
        }

        if ( is_object( $data ) ) {
            $data   = ( array ) $data;
        }
        $data   = array_merge( $this->_data, $data );

        $batch ? $method = 'batchInsert' : $method = 'insert';

        $result = false;
        try {
            if ( $this->_db->{$collection}->{$method}( $data, $options ) ) {
                $data['_id']    = ( string ) $data['_id'];
                $result         = ( object ) $data;
            }
        } catch( \MongoCursorException $e ) {
            $result         = new \stdClass();
            $result->error  = $e->getMessage();
        }
        $this->_flush();

        return $result;
    }

    protected function _limit( $limit, $offset = null ) {
        if ( $limit !== NULL && is_numeric( $limit ) && $limit >= 1 ) {
            $this->_lmt = $limit;
        }
        if ( $offset !== NULL && is_numeric( $offset ) && $offset >= 1 ) {
            $this->_ost = $offset;
        }
    }

    protected function _order_by( $key, $dir ) {
        if ( $dir == 'asc' ) {
            $this->_st[$key] = 1;
        } else if( $dir == 'desc' ) {
            $this->_st[$key] = -1;
        }
    }

    protected function _remove( $collection = "", $where = array(), $options = array() ) {
        if ( empty( $collection ) ) {
            $collection = $this->_from;
        }

        $this->_set_where( $where );

        $result = false;
        try {
            if ( $this->_db->{$collection}->remove( $this->_ws, $options ) ) {
                $result = true;
            }
        } catch( \MongoCursorException $e ) {
            $result         = new \stdClass();
            $result->error  = $e->getMessage();
        }
        $this->_flush();

        return $result;
    }

    protected function _select( $select = "" ) {
        $fields = explode( ',', $select );
        foreach ( $fields as $field ) {
            $this->_sls[trim( $field )] = true;
        }
    }

    protected function _set( $k, $v = null ) {
        if( is_array( $k ) ) {
            $this->_data        = $k;
        } else if( is_object( $k ) ) {
            $this->_data        = ( array ) $k;
        } else {
            $this->_data[$k]    = $v;
        }
    }

    protected function _update( $collection = "", $data = array(), $where = array() ) {
        if( empty( $collection ) ) {
            $collection = $this->_from;
        }

        $data = array_merge( $this->_data, $data );

        $this->_set_where( $where );

        if ( array_key_exists( '$inc', $data ) ) {
            $newdoc     = $data;
        } else if ( array_key_exists( '$set', $data ) ) {
            $newdoc     = $data;
        } else {
            $newdoc     = array( '$set' => $data );
        }

        $result         = false;
        try {
            if( $this->_db->{$collection}->update( $this->_ws, $newdoc ) ) {
                $result = ( object ) $data;
            }
        } catch( \MongoCursorException $e ) {
            $result         = new \stdClass();
            $result->error  = $e->getMessage();
        }
        $this->_flush();

        return $result;
    }

    protected function _where( $key, $value = null ) {
        if ( is_array( $key ) ) {
            foreach( $key as $k => $v ) {
                $this->_ws[$k] = $v;
            }
        } else {
            $this->_ws[$key] = $value;
        }
    }

    private function _deref( &$data ) {
        foreach( $data as $key => $value ) {
            if( is_object( $value ) || is_array( $value ) ) {
                if( is_object( $data ) ) {
                    $data->{$key} = $this->_deref( $value );
                } else {
                    $data[ $key ] = $this->_deref( $value );
                }
            }
            if( \MongoDBRef::isRef( $value ) ) {
                if( is_object( $data ) ) {
                    $data->{$key} = $this->_db->getDBRef( $value );
                } else {
                    $data[ $key ] = $this->_db->getDBRef( $value );
                }
            }
        }
        return $data;
    }

    private function _flush() {
        $this->_data    = array();
        $this->_ws      = array();
        $this->_sls     = array();
        $this->_st      = array();
        $this->_from    = "";
        $this->_lmt     = 99999;
        $this->_ost     = 0;
    }

    private function _set_where( $where = null ) {
        if ( isset( $where ) && $where !== null ) {
            if ( is_array( $where ) ) {
                $where  = array_merge( $where, $this->_ws );
                foreach ( $where as $k => $v ) {
                    if ( $k == "_id" && ( gettype( $v ) == "string" ) ) {
                        $this->_ws[$k]  = new \MongoId( $v );
                    } else {
                        $this->_ws[$k]  = $v;
                    }
                }
            } else if( is_string( $where ) ) {
                $wheres = explode( ',', $where );
                foreach ( $wheres as $wr ) {
                    $pair = explode( '=', trim( $wr ) );
                    if ( $pair[0] == "_id" ) {
                        $this->_ws[trim( $pair[0] )] = new \MongoId( trim( $pair[1] ) );
                    } else {
                        $this->_ws[trim( $pair[0] )] = trim( $pair[1] );
                    }
                }
            }
        }
    }
}