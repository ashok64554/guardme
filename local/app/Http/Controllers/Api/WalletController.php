<?php

namespace Responsive\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;

use Responsive\Http\Controllers\Controller;
use Responsive\Transaction;
use Responsive\Job;


class WalletController extends Controller
{
    //
    public function getWalletData() {
        $wallet = new Transaction();
        $wallet_data = $wallet->getAllTransactionsAndEscrowBalance();
        return response()
            ->json($wallet_data, 200);
    }


    public function JobsList(){

        $jobDetails = Job::getMyJobs();
        $data = array() ;
        foreach($jobDetails as $list){
                $data[] = [ 'id'=>$list->id ,
                            'title'=>$list->title ,
                            'calc'=>Job::calculateJobAmount($list->id)

                ];

        }

        return response()
            ->json($data, 200);
//        $jobDetails = Job::calculateJobAmount(1);


    }


    public function getTransactionsOfJobs(){
        $user_id = \Auth::user()->id;
        //echo $user_id ;
        $my_jobs = Job::select('id' ,'title')->with('getJobTransactions')->get();
       // $user_transactions = Transaction::with(['getTransactionJob'])->where('user_id' , $user_id)->get();

        //echo json_encode($user_transactions);
        return response()
            ->json($my_jobs , 200);

    }


    public function getJobTransactionDetails($id){
        $wallet_data = Transaction::where('job_id' , $id )->get();
     //  $data = ['name'=> 'maysoon' , 'age'=> 26] ;
       //echo json_encode($data);

        return response()
            ->json($wallet_data, 200);

    }


}
