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

    public function getBetRequest(Request $request)
    {

        $response = [];

        $player = $this->getPlayerData($request->player_id);

        $errors_global = $this->validateGlobal($request, $player);
        $errors_selection = $this->validateSelections($request->selections);

        if(!is_null($errors_global) || !is_null($errors_selection)){
                	
        	$response['player_id'] = $request->player_id;
        	$response['stake_amount'] = $request->stake_amount;

            if (!is_null($errors_global)) {
                $response['errors'] = $errors_global;
            }

            if(!is_null($errors_selection)){
            	$response['selections'] = array_replace_recursive($request->selections, $errors_selection);
            } else {
            	$response['selections'] = $request->selections;
            }

            return response()->json($response, 400);
        }

    	// sleep(rand(1,30));

    	$result = $this->placeBet($request, $player);

        return response()->json([], 201);
    }

    public function getPlayerData($player_id){

    	$player = PlayerModel::firstOrCreate(['id' => $player_id]);
    	
    	if(is_null($player->balance)){
    		$player->balance = 1000;
    		$player->can_proceed = 1;
    	}

    	return $player;
    }

    public function validateGlobal($request, $player)
    {
    	$errors = null;
    	$total_odds = 1;
        $rules = [
            "player_id" => "required|integer",
            "stake_amount" => "required|numeric",
            "selections" => "required|array",
            "selections.*.id" => "required|integer",
            "selections.*.odds" => "required|numeric"
        ];

        if($player->can_proceed){
        	$player->can_proceed = 0;
        	$player->save();
        } else {
        	$errors[] = $this->error[10];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors[] = $this->error[1];
        }

		$validator = Validator::make($request->all(), ["stake_amount" => "numeric|min:0.3"]); 
		if ($validator->fails()) {
            $errors[] = $this->error[2];
        }

        $validator = Validator::make($request->all(), ["stake_amount" => "numeric|max:10000"]); 
		if ($validator->fails()) {
            $errors[] = $this->error[3];
        }

        $validator = Validator::make($request->all(), ["selections" => "array|min:1"]); 
		if ($validator->fails()) {
            $errors[] = $this->error[4];
        }

        $validator = Validator::make($request->all(), ["selections" => "array|max:20"]); 
		if ($validator->fails()) {
            $errors[] = $this->error[5];
        }

        foreach($request->selections as $selection){
        	$total_odds *= $selection['odds'];
        }

        if($request->stake_amount * $total_odds > 20000){
        	$errors[] = $this->error[9];
        }

        if($player->balance < $request->stake_amount){
        	$errors[] = $this->error[11];
        }

        return $errors;
    }

    public function validateSelections($selections)
    {
    	$errors = null;
        foreach ($selections as $key => $selection) {
            if ($selection['odds'] < 1) {
                $errors[$key]['errors'][] = $this->error[6];
            } elseif ($selection['odds'] > 10000) {
                $errors[$key]['errors'][] = $this->error[7];
            }

            foreach ($selections as $k => $s) {
                if ($key < $k && $selection['id'] == $s['id']) {
                    $errors[$key]['errors'][] = $this->error[8];
                    $errors[$k]['errors'][] = $this->error[8];
                }
            }
        }
        return $errors;
    }

    public function placeBet($request, $player){
    	$bet = BetModel::create(['stake_amount' => $request->stake_amount, 'created_at' => date('Y-m-d H:i:s')]);

    	foreach($request->selections as $selection){
    		$selection = BetSelectionsModel::create(
    			['bet_id' => $bet->id,
    			'selection_id' => $selection['id'],
    			'odds' => $selection['odds']
    		]);
    	}

    	$transaction = BalanceTransactionModel::create(
    		['player_id' => $request->player_id,
    		'amount' => $player->balance - $request->stake_amount,
    		'amount_before' => $player->balance]
    	);

    	$player->balance -= $request->stake_amount;
    	$player->can_proceed = 1;
        $player->save();

        return $bet;
    }
}
