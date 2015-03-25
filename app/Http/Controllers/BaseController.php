<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class BaseController extends Controller {

    protected function _response( $result ) {
        if ( property_exists( $result, "status" ) ) {
            return response()->json( $result, $result->status );
        } else {
            return response()->json( $result );
        }
    }
}