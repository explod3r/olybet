<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\PlayerModel;
use App\Models\BetModel;
use App\Models\BetSelectionsModel;
use App\Models\BalanceTransactionModel;
use Validator;

class BetController extends Controller
{
	private $error = [
		'0' => [
			'code' => 0,
			'message' => 'Unknown error'
		],
		'1' => [
			'code' => 1,
			'message' => 'Betslip structure mismatch'
		],
		'2' => [
			'code' => 2,
			'message' => 'Minimum stake amount is 0.3'
		],
		'3' => [
			'code' => 3,
			'message' => 'Maximum stake amount is 10000'
		],
		'4' => [
			'code' => 4,
			'message' => 'Minimum number of selections is 1'
		],
		'5' => [
			'code' => 5,
			'message' => 'Maximum number of selections is 20'
		],
		'6' => [
			'code' => 6,
			'message' => 'Minimum odds are 1'
		], 
		'7' => [
			'code' => 7,
			'message' => 'Maximum odds are 10000'
		],
		'8' => [
			'code' => 8,
			'message' => 'Duplicate selection found'
		],
		'9' => [
			'code' => 9,
			'message' => 'Maximum win amount is 20000'
		],
		'10' => [
			'code' => 10,
			'message' => 'Your previous action is not finished yet '
		],
		'11' => [
			'code' => 11,
			'message' => 'Insufficient balance'
		],
	];

    public function getBetRequest(Request $request){
    	$response = [];
    	$errors = [];

    	// $response['errors'][] = ['asdfas'=>'asdfa'];
    	$errors= $this->validateBetslipStructure($request);
    	if(!is_null($errors)){
    		$response['errors'][] = $errors;
    	} 
    	// elseif(!is_null($errors)){
    	// 	$response['errors'][] = $errors;
    	// }

    	$response['selections'] = $this->validateSelections($request->selections);

    	var_dump($response);
    	die;


    	// return response()->json([], 201);
    }

    public function validateBetslipStructure($request){

    	$rules = [
    		"player_id" => "required|integer",
    		"stake_amount" => "required|numeric",
    		"selections" => "required|array",
    		"selections.*.id" => "required|integer",
    		"selections.*.odds" => "required|numeric"
    	];

    	$validator = Validator::make($request->all(), $rules);

    	if($validator->fails()){
    		return $this->error[1];
    	}
    }

    public function validateSelections($selections){
    	foreach($selections as $key => $selection){

    		if($selection['odds'] < 1){
    			$selections[$key]['errors'][] = $this->error[6];
    		} elseif ($selection['odds'] > 10000){
    			$selections[$key]['errors'][] = $this->error[7];
    		}

    		foreach($selections as $k => $s){
    			if($key < $k && $selection['id'] == $s['id']){
    				$selections[$key]['errors'][] = $this->error[8];
					$selections[$k]['errors'][] = $this->error[8];
    			}
    		}
    	}
    	return $selections;
    }
}
