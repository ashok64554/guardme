<?php

namespace Responsive\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Responsive\User;
class SearchController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
	
	public function sangvish_view()

	{
		$viewservices= DB::table('subservices')->orderBy('subname','asc')->get();
      
		$shopview=DB::table('shop')
		->leftJoin('users', 'users.email', '=', 'shop.seller_email')
		->leftJoin('rating', 'rating.rshop_id', '=', 'shop.id')
		->where('shop.status', 'approved')->orderBy('shop.id','desc')
		->groupBy('shop.id')
		->get();
				
		$data = array('viewservices' => $viewservices,'shopview' => $shopview);
		return view('search')->with($data);
	}

function getpersonnelsearch()
	{
	    $data = \request()->all();


		$query = User::where('admin','2');

		if(count($data)){

		    // todo: filter by category
            $search_category = trim($data['cat_val']);
            if($search_category){
                $query = $query->whereHas('sec_work_category', function ($q) use ($search_category){
                    $q->where('name', $search_category);
                });
            }

            // todo: filter by gender
            $search_gender = trim($data['gender']);
            if($search_gender){
                $query = $query->where('gender', $search_gender);
            }

		    // todo: filter location
            /*$search_location = trim($data['loc_val']);

            if($search_location){
                $query = $query
                    ->whereHas('address', function ($q) use ($search_location){
                        $q->where('citytown', $search_location);
                    });
            }*/

            // todo: filter user
		    $personnel_query = $data['sec_personnel'];

		    if($personnel_query){
                $search_query_array = explode(' ', trim($personnel_query));

                if(count($search_query_array)){
                    foreach ($search_query_array as $search_key){
                        $query = $query
                            ->where('name', 'LIKE', "%$search_key%")
                            ->orWhere('email', 'LIKE', "%$search_key%")
                            ->orWhere('firstname', 'LIKE', "%$search_key%")
                            ->orWhere('lastname', 'LIKE', "%$search_key%")
                        ;
                    }
                }
            }
        }

		$cats= DB::table('security_categories')->orderBy('name','asc')->get();

        $locs= DB::table('address')->distinct()->get();

        $sec_personnels = $query->with('person_address')->paginate(10);

		return view('search',compact('cats','locs','sec_personnels'));
	}
	
	function postpersonnelsearch(Request $request)
	{
		$cat = $request->cat_id;
		$loc = $request->loc_id;
		$personnel = $request->sec_personnel;


		$cats= DB::table('security_categories')->orderBy('name','asc')->get();

		$locs= DB::table('address')->get();

		if($cat !='' || $loc !='' || $personnel !='')
		{
			$persons = User::where('admin','2');
			if($personnel !='')
			{
				$persons->where('name', 'like', "$personnel%");
				
			}

			if($cat !='')
			{
				$persons->where('work_category', "$cat");
				
			}

			if($loc !='')
			{
				$persons->with('person_address')->whereHas('person_address',function($query) use ($loc){
					//dd($query);
					$query->where('id',$loc);

				});

				
			}
			$sec_personnels = $persons->paginate(10);
		}
		else{

			$sec_personnels = User::where('admin','2')->paginate(10);

		}
		//dd($sec_personnels);
		$request->flash();
		return view('search',compact('sec_personnels','cats','locs'));
	}

	function personnelprofile($id)
	{

		$person = User::with(['person_address','sec_work_category'])->find($id);
		//dd($person->work_category);

		return view('profile',compact('person'));

	}
	
	public function sangvish_homeindex($id)
	{
		
		$subview=strtolower($id);
			$results = preg_replace('/-+/', ' ', $subview); 
		
		
		
		 $services = DB::table('subservices')->where('subname', $results)->get();
		 
		 $subsearches = DB::table('shop')
		->leftJoin('seller_services', 'seller_services.shop_id', '=', 'shop.id')
		->leftJoin('rating', 'rating.rshop_id', '=', 'shop.id')
		->leftJoin('users', 'users.email', '=', 'shop.seller_email')
		->where('shop.status', '=', 'approved')
		->where('seller_services.subservice_id', '=', $services[0]->subid)
		->groupBy('shop.id')
		->get();
		
		$viewservices= DB::table('subservices')->orderBy('subname','asc')->get();
		
		$shopview=DB::table('shop')
		         ->leftJoin('rating', 'rating.rshop_id', '=', 'shop.id')
		         ->where('shop.status', '=', 'approved')
				 ->orderBy('shop.id','desc')->get();
		
		$sub_value = $id;
		
		$data = array('subsearches' => $subsearches, 'viewservices' => $viewservices, 'shopview' => $shopview, 'sub_value' => $sub_value, 'services' => $services);
            return view('search')->with($data);
		
	}
	
    public function sangvish_index(Request $request)
    {
		
       
		$datas = $request->all();
          
		  $search_text=$datas['search_text'];
		  $count= DB::table('subservices')->where('subname', $search_text)->count();
		  
		  if(!empty($count))
		  {
		  $services = DB::table('subservices')->where('subname', $search_text)->get();
		  
		   $subsearches = DB::table('shop')
		->leftJoin('seller_services', 'seller_services.shop_id', '=', 'shop.id')
		->leftJoin('rating', 'rating.rshop_id', '=', 'shop.id')
		->leftJoin('users', 'users.email', '=', 'shop.seller_email')
		->where('shop.status', '=', 'approved')
		->where('seller_services.subservice_id', '=', $services[0]->subid)
		->orderBy('shop.id','desc')
		->groupBy('shop.id')
		->get();
		  }
		 if(empty($count))
		  {
			  $services = "";
			   $subsearches = "";
		  }
		  
		  $viewservices= DB::table('subservices')->orderBy('subname','asc')->get();
		  
		  $shopview=DB::table('shop')
		         ->leftJoin('rating', 'rating.rshop_id', '=', 'shop.id')
		         ->where('shop.status', '=', 'approved')
				 ->orderBy('shop.id','desc')->get();
		  
		  
		  $sub_value="";
		 
      
		
		$data = array('services' => $services, 'viewservices' => $viewservices, 'shopview' => $shopview, 'subsearches' => $subsearches, 'count' => $count,
		'search_text' => $search_text, 'sub_value' => $sub_value);
            return view('search')->with($data);
    }
	
	public function sangvish_search(Request $request)
	{
		
		 $shopview=DB::table('shop')
		 ->leftJoin('users', 'users.email', '=', 'shop.seller_email')
		 ->leftJoin('rating', 'rating.rshop_id', '=', 'shop.id')
		 ->where('shop.status', 'approved')->orderBy('shop.id','desc')->get();
		
		 $viewservices= DB::table('subservices')->orderBy('subname','asc')->get();
		 
		 $datas = $request->all();
		 
		 
		 
		 
		 
		 $approved='approved';
		 
		 
		 if(!empty($datas["langOpt"]))
		 {
		 
		 $langOpt=$datas["langOpt"];
		 $newlang="";
		 $vvnew="";
		 $views="";
		 foreach($langOpt as $langs)
		 {
			 $viewname= DB::table('subservices')->where("subid", "=" , $langs)->get();
			 $views .=$viewname[0]->subname.",";
			 $newlang .=$langs.",";
			 $vvnew .="'".$langs."',";
		 }
		 $viewnames =rtrim($views,",");
		 $selservice =rtrim($newlang,",");
		 $welservice =rtrim($vvnew,",");
		 
		 
		 $newsearches = DB::table('shop')
		->leftJoin('seller_services', 'seller_services.shop_id', '=', 'shop.id')
		->leftJoin('rating', 'rating.rshop_id', '=', 'shop.id')
		->leftJoin('users', 'users.email', '=', 'shop.seller_email')
		->where('shop.status', '=', $approved)
		 
		
		->whereRaw('FIND_IN_SET(seller_services.subservice_id,"'.$selservice.'")')
		
		 ->groupBy('seller_services.shop_id')
		 
         ->get();
		 
		 
		 
		 
		 
		 $count = DB::table('shop')
		->leftJoin('seller_services', 'seller_services.shop_id', '=', 'shop.id')
		->leftJoin('rating', 'rating.rshop_id', '=', 'shop.id')
		->leftJoin('users', 'users.email', '=', 'shop.seller_email')
		->where('shop.status', '=', $approved)
		 
		
		->whereRaw('FIND_IN_SET(seller_services.subservice_id,"'.$selservice.'")')
		
		
		 ->groupBy('seller_services.shop_id')
		 
         ->count();
		 
		 
		
		 
		 $data = array('viewservices' => $viewservices, 'shopview' => $shopview, 'newsearches' => $newsearches, 'selservice' => $selservice, 'viewnames' => $viewnames,
		 'count' => $count);
		 }
		 if(!empty($datas['city']))
		 {
			  $newsearches = DB::table('shop')
		->leftJoin('seller_services', 'seller_services.shop_id', '=', 'shop.id')
		->leftJoin('rating', 'rating.rshop_id', '=', 'shop.id')
		->leftJoin('users', 'users.email', '=', 'shop.seller_email')
		->where('shop.status', '=', $approved)
			->where('shop.city','LIKE','%'.$datas['city'].'%')
			 ->groupBy('seller_services.shop_id')
		 
         ->get();
		 
		 
		 $count = DB::table('shop')
		->leftJoin('seller_services', 'seller_services.shop_id', '=', 'shop.id')
		->leftJoin('rating', 'rating.rshop_id', '=', 'shop.id')
		->leftJoin('users', 'users.email', '=', 'shop.seller_email')
		->where('shop.status', '=', $approved)
			->where('shop.city','LIKE','%'.$datas['city'].'%')
			 ->groupBy('seller_services.shop_id')
		 
         ->count();
		 
		 
		 $viewnames =$datas['city'];
		 
			$data = array('viewservices' => $viewservices, 'shopview' => $shopview, 'newsearches' => $newsearches, 'viewnames' => $viewnames, 'count' => $count);
		 }
		 
		if((!empty($datas['city'])) && (!empty($datas["langOpt"])))
		 {
			 
			 $langOpt=$datas["langOpt"];
		 $newlang="";
		 $vvnew="";
		 foreach($langOpt as $langs)
		 {
			 $newlang .=$langs.",";
			 $vvnew .="'".$langs."',";
		 }
		 $selservice =rtrim($newlang,",");
		 $welservice =rtrim($vvnew,",");
		 
		 
		 $newsearches = DB::table('shop')
		->leftJoin('seller_services', 'seller_services.shop_id', '=', 'shop.id')
		->leftJoin('rating', 'rating.rshop_id', '=', 'shop.id')
		->leftJoin('users', 'users.email', '=', 'shop.seller_email')
		->where('shop.status', '=', $approved)
		 ->where('shop.city','LIKE','%'.$datas['city'].'%')
		 
		
		->whereRaw('FIND_IN_SET(seller_services.subservice_id,"'.$selservice.'")')
		
		 ->groupBy('seller_services.shop_id')
		 
         ->get();
		 
		 
		  $count = DB::table('shop')
		->leftJoin('seller_services', 'seller_services.shop_id', '=', 'shop.id')
		->leftJoin('rating', 'rating.rshop_id', '=', 'shop.id')
		->leftJoin('users', 'users.email', '=', 'shop.seller_email')
		->where('shop.status', '=', $approved)
		 ->where('shop.city','LIKE','%'.$datas['city'].'%')
		
		->whereRaw('FIND_IN_SET(seller_services.subservice_id,"'.$selservice.'")')
		
		 ->groupBy('seller_services.shop_id')
		 
         ->count();
		 
		 $viewnames =$datas['city'];
		 
			 
			 $data = array('viewservices' => $viewservices, 'shopview' => $shopview, 'newsearches' => $newsearches, 'selservice' => $selservice, 'viewnames' => $viewnames,
			 'count' => $count); 
		 }
		 
		 
		 if((empty($datas['city'])) && (empty($datas["langOpt"])))
		 {
			 
		$viewnames="";	 
		$data = array('viewservices' => $viewservices, 'shopview' => $shopview,  'viewnames' => $viewnames);
		 }
            return view('shopsearch')->with($data);
	}
	
	
	
	
	
	
	
	
	
	
	
}
