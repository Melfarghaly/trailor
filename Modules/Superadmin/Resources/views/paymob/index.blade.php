@extends('layouts.app')
@section('title', __('superadmin::lang.superadmin') . ' | Business')

@section('content')
@include('superadmin::layouts.nava')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>المدفوعات
        <small>مدفوعات paymob</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
    @component('components.filters', ['title' => __('report.filters')])
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('business_id',  __('النشاط') . ':') !!}
                {!! Form::select('business_id', $business, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
         <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('payout_status',  __('حالة الصرف ') . ':') !!}
                {!! Form::select('payout_status', ['due'=>'Due','paid'=>'Paid'], null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        <div class="col-md-3">
        <div class="form-group">
                {!! Form::label('sell_list_filter_date_range', __('report.date_range') . ':') !!}
                {!! Form::text('sell_list_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
            </div>
        </div>
        
    @endcomponent
	<div class="box box-solid">
        <div class="box-header">
            <h3 class="box-title">&nbsp;</h3>
        
        </div>

        <div class="box-body">
            @can('superadmin')
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="superadmin_business_table">
                        <thead>
                            <tr>
                                <th>
                                    التاريخ
                                </th>
                                <th>@lang( 'superadmin::lang.business_name' )</th>
                                <th>الرقم المرجعي</th>
                                <th>المبلغ</th>
                                <th>رقم الفاتورة</th>
                                <th>رقم طلب الدفع</th>
                                <th>رقم عملية الدفع</th>
                                <th>بوابة الدفع</th>
                                <th> حالة الصرف </th>
                                <th>@lang( 'superadmin::lang.action' )</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th colspan="3">المجموع</th>
                                <th class="footer_total_paid"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endcan
        </div>
    </div>

</section>
<!-- /.content -->

@endsection
<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"></div>
@section('javascript')
<script type="text/javascript">
    // change_status button
        $(document).on('click', 'button.change_status', function(){
            $("div#statusModal").load($(this).data('href'), function(){
                $(this).modal('show');
                $("form#status_change_form").submit(function(e){
                    e.preventDefault();
                    var url = $(this).attr("action");
                    var data = $(this).serialize();
                    $.ajax({
                        method: "POST",
                        dataType: "json",
                        data: data,
                        url: url,
                        success:function(result){
                            if( result.success == true){
                                $("div#statusModal").modal('hide');
                                toastr.success(result.msg);
                                superadmin_subscription_table.ajax.reload();
                            }else{
                                toastr.error(result.msg);
                            }
                        }
                    });
                });
            });
        });
    $(document).ready( function(){
          //Date range as a button
    $('#sell_list_filter_date_range').daterangepicker(
        dateRangeSettings,
        function (start, end) {
            $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            superadmin_business_table.ajax.reload();
        }
    );
    $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
        $('#sell_list_filter_date_range').val('');
        superadmin_business_table.ajax.reload();
    });
        
        superadmin_business_table = $('#superadmin_business_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{action('\Modules\Superadmin\Http\Controllers\PaymobController@index')}}",
                data: function(d) {
                    if($('#sell_list_filter_date_range').val()) {
                        var start = $('#sell_list_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                        var end = $('#sell_list_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                        d.start_date = start;
                        d.end_date = end;
                    }
                    d.business_id = $('#business_id').val();
                    d.payout_status = $('#payout_status').val();

                },
            },
            aaSorting: [[0, 'desc']],
            columns: [
                { data: 'created_at', name: 'created_at' },
                { data: 'business_name', name: 'business_name' },
                { data: 'payment_ref_no', name: 'payment_ref_no' },
                { data: 'amount', name: 'amount', searchable: false},
                { data: 'invoice_no', name: 'invoice_no' },
                { data: 'order_id', name: 'order_id' },
                { data: 'payment_id', name: 'payment_id' },
                { data: 'gateway', name: 'gateway' },
                { data: 'payout_status', name: 'payout_status' },
                { data: 'action', name: 'action' },
               
            ],
             "fnDrawCallback": function (oSettings) {
            __currency_convert_recursively($('#superadmin_business_table'));
        },
        "footerCallback": function ( row, data, start, end, display ) {
            var footer_sale_total = 0;
            var footer_total_paid = 0;
            var footer_total_remaining = 0;
            var footer_total_sell_return_due = 0;
            for (var r in data){
              
                footer_total_paid += $(data[r].amount).data('orig-value') ? parseFloat($(data[r].amount).data('orig-value')) : 0;
               
            }

           
            $('.footer_total_paid').html(__currency_trans_from_en(footer_total_paid));
            
        },
        });

        $('#business_id, #sell_list_filter_date_range,#payout_status').change( function(){
            superadmin_business_table.ajax.reload();
        });
    });
    $(document).on('click', 'a.delete_business_confirmation', function(e){
        e.preventDefault();
        swal({
            title: LANG.sure,
            text: "Once deleted, you will not be able to recover this business!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((confirmed) => {
            if (confirmed) {
                window.location.href = $(this).attr('href');
            }
        });
    });
</script>

@endsection