<?php

namespace Modules\Superadmin\Http\Controllers;

use App\Business;
use App\Product;
use App\Transaction;
use App\TransactionPayment;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use App\VariationLocationDetails;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Superadmin\Notifications\PasswordUpdateNotification;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;
use Modules\Superadmin\Entities\Package;

class PaymobController extends BaseController
{
    protected $businessUtil;
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(BusinessUtil $businessUtil, ModuleUtil $moduleUtil)
    {
        $this->businessUtil = $businessUtil;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $date_today = \Carbon::today();
            $payments=TransactionPayment::join('transactions as t','transaction_payments.transaction_id','t.id')
            ->leftJoin('business as b','t.business_id','b.id')
            ->where('transaction_payments.gateway','PayMob')
            ->select(
                't.invoice_no',
                't.invoice_token',
                'b.name as business_name',
                't.business_id',
                'transaction_payments.amount',
                'transaction_payments.payment_ref_no',
                'transaction_payments.gateway',
                'transaction_payments.note',
                'transaction_payments.created_at',
                'transaction_payments.id',
                'transaction_payments.payout_status'
                );
              if(!empty(request()->get('business_id'))){
                  $payments->where('t.business_id',request()->get('business_id'));
              }
              if(!empty(request()->get('payout_status'))){
                  $payments->where('transaction_payments.payout_status',request()->get('payout_status'));
              }
                if (!empty(request()->start_date) && !empty(request()->end_date)) {
                    $start = request()->start_date;
                    $end =  request()->end_date;
                    $payments->whereDate('transaction_payments.created_at', '>=', $start)
                            ->whereDate('transaction_payments.created_at', '<=', $end);
                }

            return Datatables::of($payments)
              ->editColumn(
                    'amount',
                    '<span class="amount" data-orig-value="{{$amount}}">@format_currency($amount)</span>'
                )
                ->editColumn('payout_status',function ($row){
                    $calss='';
                    if($row->payout_status=='due'){
                        $calss='bg-yellow';
                        $value="مستحق الصرف";
                    }else if($row->payout_status=='paid'){
                        $calss='bg-green';
                         $value="مصروف";
                    }
                   return  '<button data-href="/superadmin/paymob/'.$row->id.'/edit" class="btn btn-default btn-xs change_status" data-toggle="modal" data-target="#statusModal"> 
                   <span class="label '.$calss.'">'.$value.'</span>
                   </button>';
                  
                })
                ->addColumn('action', function($row) {
                    $html = '<a href="/invoice/'.$row->invoice_token.'"
                               target="_blank" class="btn btn-info btn-xs"> عرض </a>';
                    return $html;
                })
             
                ->editColumn('created_at', '{{@format_datetime($created_at)}}')
                ->addColumn('order_id',function($row){
                      $paymob_data=json_decode($row->note);
                      if(is_object($paymob_data))
                            return $paymob_data->order_id;
                    return '--';
                })
                ->addColumn('payment_id',function($row){
                      $paymob_data=json_decode($row->note);
                      if(is_object($paymob_data))
                            return $paymob_data->trxn;
                    return '--';
                })
                ->rawColumns(['action', 'is_active', 'created_at','amount','payout_status'])
                ->make(true);
        }

        $business_id = request()->session()->get('user.business_id');

        $packages = Package::listPackages()->pluck('name', 'id');

        $subscription_statuses = [
            'subscribed' => __('superadmin::lang.subscribed'),
            'expired' => __('report.expired'),
            '30' => __('superadmin::lang.expiring_in_one_month'),
            '7' => __('superadmin::lang.expiring_in_7_days'),
            '3' => __('superadmin::lang.expiring_in_3_days'),
        ];

        $last_transaction_date = [
            'today' => __('home.today'),
            'yesterday' => __('superadmin::lang.yesterday'),
            'this_week' => __('home.this_week'),
            'this_month' => __('home.this_month'),
            'last_month' => __('superadmin::lang.last_month'),
            'this_year' => __('superadmin::lang.this_year'),
            'last_year' => __('superadmin::lang.last_year')
        ];
        $business=Business::all()->pluck('name','id');
        return view('superadmin::paymob.index')
            ->with(compact('business_id', 'packages', 'subscription_statuses', 'last_transaction_date','business'));
    }

    private function filterTransactionDate($query, $filter, $operator)
    {
        if ($filter == 'today') {
            $today = \Carbon::today()->format('Y-m-d');
            $query->whereRaw("(SELECT COUNT(id) FROM transactions as t WHERE t.business_id = business.id AND DATE(t.transaction_date) = '$today') $operator 0");
        } else if ($filter == 'yesterday') {
            $yesterday = \Carbon::yesterday()->format('Y-m-d');
            $query->whereRaw("(SELECT COUNT(id) FROM transactions as t WHERE t.business_id = business.id AND DATE(t.transaction_date) >= '$yesterday') $operator 0");
        } else if ($filter == 'this_week') {
            $this_week = \Carbon::today()->subDays(7)->format('Y-m-d');
            $query->whereRaw("(SELECT COUNT(id) FROM transactions as t WHERE t.business_id = business.id AND DATE(t.transaction_date) >= '$this_week') $operator 0");
        } else if ($filter == 'this_month') {
            $this_month = \Carbon::today()->firstOfMonth()->format('Y-m-d');
            $query->whereRaw("(SELECT COUNT(id) FROM transactions as t WHERE t.business_id = business.id AND DATE(t.transaction_date) >= '$this_month') $operator 0");
        } else if ($filter == 'last_month') {
            $last_month = \Carbon::today()->subDays(30)->firstOfMonth()->format('Y-m-d');
            $query->whereRaw("(SELECT COUNT(id) FROM transactions as t WHERE t.business_id = business.id AND DATE(t.transaction_date) >= '$last_month') $operator 0");
        } else if ($filter == 'this_year') {
            $this_year = \Carbon::today()->firstOfYear()->format('Y-m-d');
            $query->whereRaw("(SELECT COUNT(id) FROM transactions as t WHERE t.business_id = business.id AND DATE(t.transaction_date) >= '$this_year') $operator 0");
        } else if ($filter == 'last_year') {
            $last_year = \Carbon::today()->subYear()->firstOfYear()->format('Y-m-d');
            $query->whereRaw("(SELECT COUNT(id) FROM transactions as t WHERE t.business_id = business.id AND DATE(t.transaction_date) >= '$last_year') $operator 0");
        }

        return $query;
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $currencies = $this->businessUtil->allCurrencies();
        $timezone_list = $this->businessUtil->allTimeZones();

        $accounting_methods = $this->businessUtil->allAccountingMethods();

        $months = [];
        for ($i=1; $i<=12 ; $i++) {
            $months[$i] = __('business.months.' . $i);
        }

        $is_admin = true;

        $packages = Package::active()->orderby('sort_order')->pluck('name', 'id');
        $gateways = $this->_payment_gateways();

        return view('superadmin::business.create')
            ->with(compact(
                'currencies',
                'timezone_list',
                'accounting_methods',
                'months',
                'is_admin',
                'packages',
                'gateways'
            ));
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            //Create owner.
            $owner_details = $request->only(['surname', 'first_name', 'last_name', 'username', 'email', 'password']);
            $owner_details['language'] = env('APP_LOCALE');
            
            $user = User::create_user($owner_details);

            $business_details = $request->only(['name', 'start_date', 'currency_id', 'tax_label_1', 'tax_number_1', 'tax_label_2', 'tax_number_2', 'time_zone', 'accounting_method', 'fy_start_month']);

            $business_location = $request->only(['name', 'country', 'state', 'city', 'zip_code', 'landmark', 'website', 'mobile', 'alternate_number']);
                
            //Create the business
            $business_details['owner_id'] = $user->id;
            if (!empty($business_details['start_date'])) {
                $business_details['start_date'] = $this->businessUtil->uf_date($business_details['start_date']);
            }
                
            //upload logo
            $logo_name = $this->businessUtil->uploadFile($request, 'business_logo', 'business_logos', 'image');
            if (!empty($logo_name)) {
                $business_details['logo'] = $logo_name;
            }
            
            //default enabled modules
            $business_details['enabled_modules'] = ['purchases','add_sale','pos_sale','stock_transfers','stock_adjustment','expenses'];
            
            //created_by
            $business_details['created_by'] = $request->session()->get('user.id');
            
            $business = $this->businessUtil->createNewBusiness($business_details);

            //Update user with business id
            $user->business_id = $business->id;
            $user->save();

            $this->businessUtil->newBusinessDefaultResources($business->id, $user->id);
            $new_location = $this->businessUtil->addLocation($business->id, $business_location);

            //create new permission with the new location
            Permission::create(['name' => 'location.' . $new_location->id ]);

            $subscription_details = $request->only(['package_id', 'paid_via', 'payment_transaction_id']);

            //Add subscription if present
            if (!empty($subscription_details['package_id']) && !empty($subscription_details['paid_via'])) {
                $subscription =  $this->_add_subscription($business->id, $subscription_details['package_id'], $subscription_details['paid_via'], $subscription_details['payment_transaction_id'],$request->session()->get('user.id'), true);
            }
            
            DB::commit();

            //Module function to be called after after business is created
            if (config('app.env') != 'demo') {
                $this->moduleUtil->getModuleData('after_business_created', ['business' => $business]);
            }

            $output = ['success' => 1,
                            'msg' => __('business.business_created_succesfully')
                        ];

            return redirect()
                ->action('\Modules\Superadmin\Http\Controllers\BusinessController@index')
                ->with('status', $output);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => 0,
                            'msg' => __('messages.something_went_wrong')
                        ];

            return back()->with('status', $output)->withInput();
        }
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($business_id)
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $business = Business::with(['currency', 'locations', 'subscriptions', 'owner'])->find($business_id);
        
        $created_id = $business->created_by;

        $created_by = !empty($created_id) ? User::find($created_id) : null;

        return view('superadmin::business.show')
            ->with(compact('business', 'created_by'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit($id)
    {
        
        
        $tp=\DB::table('transaction_payments')->where('id',$id)->first();
        $status=['due'=>'Due','paid'=>'Paid'];
        return view('superadmin::paymob.edit',compact('status','tp'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,$id)
    {
        $updated=\DB::table('transaction_payments')->where('id',$id)->update([
            'payout_status'=>$request->payout_status
            ]);
            if($updated){
                $output=[
                    'success'=>true,
                    'msg'=>'تم التحديث بنجاح '
                    ];
                
            }else{
                $output=[
                    'success'=>false,
                    'msg'=>'حدث خطأ   '
                    ];
                
            }
            return redirect()->to('/superadmin/paymob')->with('status',$output);
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $notAllowed = $this->businessUtil->notAllowedInDemo();
            if (!empty($notAllowed)) {
                return $notAllowed;
            }

            //Check if logged in busines id is same as deleted business then not allowed.
            $business_id = request()->session()->get('user.business_id');
            if ($business_id == $id) {
                $output = ['success' => 0, 'msg' => __('superadmin.lang.cannot_delete_current_business')];
                return back()->with('status', $output);
            }

            DB::beginTransaction();

            //Delete related products & transactions.
            $products_id = Product::where('business_id', $id)->pluck('id')->toArray();
            if (!empty($products_id)) {
                VariationLocationDetails::whereIn('product_id', $products_id)->delete();
            }
            Transaction::where('business_id', $id)->delete();

            Business::where('id', $id)
                ->delete();

            DB::commit();

            $output = ['success' => 1, 'msg' => __('lang_v1.success')];
            return redirect()
                ->action('\Modules\Superadmin\Http\Controllers\BusinessController@index')
                ->with('status', $output);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => 0,
                            'msg' => __('messages.something_went_wrong')
                        ];

            return back()->with('status', $output)->withInput();
        }
    }

    /**
     * Changes the activation status of a business.
     * @return Response
     */
    public function toggleActive(Request $request, $business_id, $is_active)
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $notAllowed = $this->businessUtil->notAllowedInDemo();
        if (!empty($notAllowed)) {
            return $notAllowed;
        }
            
        Business::where('id', $business_id)
            ->update(['is_active' => $is_active]);

        $output = ['success' => 1,
                    'msg' => __('lang_v1.success')
                ];
        return back()->with('status', $output);
    }

    /**
     * Shows user list for a particular business
     * @return Response
     */
    public function usersList($business_id)
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $user_id = request()->session()->get('user.id');

            $users = User::where('business_id', $business_id)
                        ->where('id', '!=', $user_id)
                        ->where('is_cmmsn_agnt', 0)
                        ->select(['id', 'username',
                            DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"), 'email']);

            return Datatables::of($users)
                ->addColumn(
                    'role',
                    function ($row) {
                        $role_name = $this->moduleUtil->getUserRoleName($row->id);
                        return $role_name;
                    }
                )
                ->addColumn(
                    'action',
                    '@can("user.update")
                        <a href="#" class="btn btn-xs btn-primary update_user_password" data-user_id="{{$id}}" data-user_name="{{$full_name}}"><i class="glyphicon glyphicon-edit"></i> @lang("superadmin::lang.update_password")</a>
                        &nbsp;
                    @endcan'
                )
                ->filterColumn('full_name', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->removeColumn('id')
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    /**
     * Updates user password from superadmin
     * @return Response
     */
    public function updatePassword(Request $request)
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $notAllowed = $this->businessUtil->notAllowedInDemo();
            if (!empty($notAllowed)) {
                return $notAllowed;
            }
        
            $user = User::findOrFail($request->input('user_id'));
            $user->password = Hash::make($request->input('password'));
            $user->save();

            //Send password update notification
            if ($this->moduleUtil->IsMailConfigured()) {
                $user->notify(new PasswordUpdateNotification($request->input('password')));
            }

            $output = ['success' => 1,
                        'msg' => __("superadmin::lang.password_updated_successfully")
                    ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }

        return $output;
    }
}
