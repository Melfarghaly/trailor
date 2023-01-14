
@extends('layouts.app')

@section('title', __('project::lang.invoices'))
<style>
#image-select {
    background-repeat: no-repeat;
    background-position: right center;
    background-size: auto 100%;
}
.custom-select {
    background-repeat: no-repeat;
    background-position: right center;
    background-size: auto 100%;
}

.custom-select option {
    background-repeat: no-repeat;
    background-position: left center;
    background-size: 25px 25px;
    padding-left: 35px;
}
</style>
@section('content')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<section class="content">
	<h1>
		<i class="fa fa-file"></i>
    	@lang('project::lang.invoice')
    	<small>@lang('project::lang.create')</small>
    </h1>
    <!-- form open -->
    {!! Form::open(['action' => '\Modules\Project\Http\Controllers\InvoiceController@store', 'id' => 'invoice_form', 'method' => 'post']) !!}
		<div class="box box-primary">
			<div class="box-body">
				<div class="row">
					<div class="col-md-6 hide">
						<div class="form-group">
							{!! Form::label('pjt_title', __('project::lang.title') . ':*' )!!}
	                        {!! Form::text('pjt_title', 'null', ['class' => 'form-control', 'required' ]) !!}
						</div>
					</div>
					<!-- project_id -->
					{!! Form::hidden('pjt_project_id', $project->id, ['class' => 'form-control']) !!}
					<div class="col-md-6">
						<div class="form-group">
							{!! Form::label('invoice_scheme_id', __('invoice.invoice_scheme') . ':*' )!!}
	                        {!! Form::select('invoice_scheme_id', $invoice_schemes, $default_scheme->id, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required', 'style' => 'width: 100%;']); !!}
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
						    <div class="input-group">
							    {!! Form::label('contact_id', __('role.customer') . ':*' )!!}
	                            {!! Form::select('contact_id', $customers, $project->contact_id, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required', 'style' => 'width: 100%;']); !!}
						    	<span class="input-group-btn">
                					<button type="button" class="btn btn-default bg-white btn-flat add_new_customer" style="margin-top: 25px;" data-name=""  @if(!auth()->user()->can('customer.create')) disabled @endif><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                				</span>
						    </div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							{!! Form::label('location_id', __('business.business_location') . ':*' )!!}
	                        {!! Form::select('location_id', $business_locations,$defalut_location->id, ['class' => 'form-control', 'placeholder' => __('messages.please_select'), 'required', 'style' => 'width: 100%;']); !!}
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-4">
						<div class="form-group">
							{!! Form::label('transaction_date', __('project::lang.invoice_date') . ':*' )!!}
	                        {!! Form::text('transaction_date',  @format_datetime('now'), ['class' => 'form-control date-time-picker','required']); !!}
						</div>
					</div>
					<div class="col-md-4">
	                    <div class="form-group">
	                       <div class="multi-input">
				              {!! Form::label('pay_term_number', __('contact.pay_term') . ':') !!} @show_tooltip(__('tooltip.pay_term'))
				              <br/>
				              {!! Form::number('pay_term_number', null, ['class' => 'form-control width-40 pull-left', 'placeholder' => __('contact.pay_term')]); !!}
				              {!! Form::select('pay_term_type', 
				              	['months' => __('lang_v1.months'), 
				              		'days' => __('lang_v1.days')], 
				              		null, 
				              	['class' => 'form-control width-60 pull-left','placeholder' => __('messages.please_select')]); !!}
				            </div>
	                    </div>
	                </div>
	                <div class="col-md-4">
						<div class="form-group">
							{!! Form::label('status', __('sale.status') . ':*' )!!}
	                        {!! Form::select('status', $statuses, null, ['class' => 'form-control', 'placeholder' => __('messages.please_select'), 'required', 'style' => 'width: 100%;']); !!}
						</div>
					</div>
				</div>
			</div>
		</div> <!-- /box -->
		
		<div class="box box-primary">
			<div class="box-header">
				<h3 class="box-title">
						التسعير
				</h3>
			</div>
			<div class="box-body">
				<div class="col-md-12">
					<div class="col-md-3">
						<label>@lang('project::lang.task'):*</label>
					</div>
					<div class="col-md-2">
						<label>@lang('project::lang.rate'):*</label>
					</div>
					<div class="col-md-2">
						<label>@lang('project::lang.qty'):*</label>
					</div>
					<div class="col-md-2">
						<label>@lang('business.tax')(%):</label>
					</div>
					<div class="col-md-2">
						<label>@lang('receipt.total'):*</label>
					</div>
					<div class="col-md-1">
					</div>
				</div>
				<div class="invoice_lines">
					<div class="col-md-12 il-bg invoice_line">
						<div class="mt-10">
							<div class="col-md-3">
								<div class="form-group">
									<div class="input-group">
										{!! Form::text('task[]', null, ['class' => 'form-control', 'required' ]) !!}
										<span class="input-group-btn">
									        <button class="btn btn-default toggle_description" type="button">
												<i class="fa fa-info-circle text-info" data-toggle="tooltip" title="@lang('project::lang.toggle_invoice_task_description')"></i>
									        </button>
									    </span>
									</div>
								</div>
							</div>
							<div class="col-md-2">
								<div class="form-group">
									{!! Form::text('rate[]', null, ['class' => 'form-control rate input_number', 'required' ]) !!}
								</div>
							</div>
							<div class="col-md-2">
								<div class="form-group">
									{!! Form::text('quantity[]', null, ['class' => 'form-control quantity input_number', 'required' ]) !!}
								</div>
							</div>
							<div class="col-md-2">
								<div class="form-group">
									{!! Form::select('tax_rate_id[]', $taxes, null, [ 'class' => 'form-control tax'], $tax_attributes); !!}

								</div>
							</div>
							<div class="col-md-2">
								<div class="form-group">
									{!! Form::text('total[]', null, ['class' => 'form-control total input_number', 'required', 'readonly']) !!}
								</div>
							</div>
							<div class="col-md-11">
								<div class="form-group description" style="display: none;">
									{!! Form::textarea('description[]', null, ['class' => 'form-control ', 'placeholder' => __('lang_v1.description'), 'rows' => '3']); !!}
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-4 col-md-offset-4">
					<br>

					<button type="button" class="btn btn-block btn-primary btn-sm add_invoice_line hide">
						@lang('project::lang.add_a_row')
						<i class="fa fa-plus-circle"></i>
					</button>
				</div>
			</div>
			<!-- including invoice line row -->
			@includeIf('project::invoice.partials.invoice_line_row')
		</div>  <!-- /box -->
		<div class="box box-primary">
			<div class="box-header with-border">
				<h3 class="box-title">
					مواصفات
					<i class="ace-icon fa fa-money"></i>
				</h3>
			</div>
			@php
				$measerments_text=\App\Measurement::where('business_id',\Auth()->user()->business_id)->where('type','text')->get();
				$measerments_select=\App\Measurement::where('business_id',\Auth()->user()->business_id)->where('type','select')->get();
			@endphp
			<div class="box-body">
				<div class="row">
					<?php //dd($measerments); ?>
					@foreach ($measerments_text as $measuer)
					@if($measuer->type=='text')
						<div class="col-md-2">
							<div class="form-group">
								<label class="control-label ">{{ $measuer->label }}</label>
								<input type="text" name="options[{{ $measuer->key }}]" class="form-control" placeholder="">
							</div>
						</div>
					@endif
					@endforeach
					
				</div>
				<div class="row">	
					@foreach ($measerments_select as $measuer)
						<?php 
							$options = json_decode($measuer->options, true);
							$options = (object) $options;
						?>
						<div class="col-md-4">
							<div id="carousel-example-generic-{{ $measuer->key }}" class="carousel slide" data-ride="carousel">
								<!-- Wrapper for slides -->
								<div class="carousel-inner">
									@foreach($options as $option)
										<div class="item @if($loop->iteration==1) active @endif" id="{{  $measuer->key }}_{{ $loop->iteration }}" >
											<center>
												<img src="/uploads/{{ $option['image'] }}" alt="Item {{ $loop->iteration }}">
											</center>
											<div class="carousel-caption">
												<div class="radio">
													<label>
														<input class="form-check-input" id="input_{{  $measuer->key }}_{{ $loop->iteration }}" type="radio" name="options[{{ $measuer->key }}]" value="{{ $option['label'] ?? $option['image'] }}" @if($loop->iteration==1) checked @endif >
														{{ $option['label'] ?? 'item'}}
													</label>
												</div>
											</div>
										</div>
									@endforeach
								</div>
								<!-- Controls -->
								<a class="left carousel-control" href="#carousel-example-generic-{{ $measuer->key }}" role="button" data-slide="prev">
									<span class="glyphicon glyphicon-chevron-left glyphicon_{{ $measuer->key }}"></span>
								</a>
								<a class="right carousel-control" href="#carousel-example-generic-{{ $measuer->key }}" role="button" data-slide="next">
									<span class="glyphicon glyphicon-chevron-right  glyphicon_{{ $measuer->key }}"></span>
								</a>
							</div>
						</div>
						<script>
							$(document).ready(function(){
								debugger;
								$("#carousel-example-generic-{{ $measuer->key }}").carousel( { interval: 0 });
							});
							$(document).on('click','.glyphicon_{{ $measuer->key }}',function(){
								
								setTimeout(() => {
								
									debugger;
									var activeItemId = $("#carousel-example-generic-{{ $measuer->key }} .carousel-inner .active").attr("id");
									//var currentItemId = $("#carousel-example-generic-{{ $measuer->key }}.active").attr('id');
									$("input[id='input_" + activeItemId + "']").prop('checked', true);
							
								}, 1000);
								
								//var currentItemId = $("#carousel-example-generic-{{ $measuer->key }}.carousel-inner .active input").prop('checked', true);
								//$("input[name='item']").prop('checked', false);
								
							});
						</script>
						
				@endforeach
				</div>
			</div>
		</div>
		<div class="box box-primary">
			<div class="box-body">
				<div class="row">
					<div class="col-md-6 col-md-offset-10">
						<b>@lang('sale.subtotal'):</b>
						<span class="subtotal display_currency" data-currency_symbol="true" >0.00</span>
						<input type="hidden" name="total_before_tax" id="subtotal" value="0.00">
					</div>
				</div> <br>
				<div class="row">
					<div class="col-md-6">
						{!! Form::label('discount_type', __('sale.discount_type') . ':' )!!}
	                    {!! Form::select('discount_type', $discount_types, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'style' => 'width: 100%;']); !!}
					</div>
					<div class="col-md-6">
						{!! Form::label('discount_amount', __('sale.discount_amount') . ':' )!!}
	                    {!! Form::text('discount_amount', null, ['class' => 'form-control input_number']) !!}
					</div>
				</div> <br>

				<div class="row">
					<div class="col-md-6 col-md-offset-6">
						<b>@lang('project::lang.invoice_total'):</b>
						<span class="invoice_total display_currency" data-currency_symbol="true" >0.00</span>
						<input type="hidden" name="final_total" id="invoice_total" value="0.00">
					</div>
				</div> <br>

				<div class="row">
					<div class="col-md-12">
						<div class="form-group">
	                        {!! Form::label('staff_note', __('project::lang.terms') . ':') !!}
	                        {!! Form::textarea('staff_note', null, ['class' => 'form-control ', 'rows' => '3']); !!}
	                    </div>
					</div>
					<div class="col-md-12">
						<div class="form-group">
	                        {!! Form::label('additional_notes', __('project::lang.notes') . ':') !!}
	                        {!! Form::textarea('additional_notes', null, ['class' => 'form-control ', 'rows' => '3']); !!}
	                    </div>
					</div>
				</div>
				<button type="submit" class="btn btn-primary pull-right">
	                @lang('messages.save')
	            </button>
			</div>
		</div> <!-- /box -->
	{!! Form::close() !!} <!-- /form close -->
</section>
<div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
	@include('contact.create', ['quick_add' => true])
</div>
<link rel="stylesheet" href="{{ asset('modules/project/sass/project.css?v=' . $asset_v) }}">
@endsection
@section('javascript')
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>

<script type="text/javascript">

  $(document).ready( function(){
    $('#transaction_date').datetimepicker({
      format: moment_date_format + ' ' + moment_time_format
    });
  });
</script>

<script src="{{ asset('modules/project/js/project.js?v=' . $asset_v) }}"></script>
<script>
    
    $(document).on('click', '.add_new_customer', function() {
        $('#customer_id').select2('close');
        var name = $(this).data('name');
        $('.contact_modal')
            .find('input#name')
            .val(name);
        $('.contact_modal')
            .find('select#contact_type')
            .val('customer')
            .closest('div.contact_type_div')
            .addClass('hide');
        $('.contact_modal').modal('show');
    });
    $('form#quick_add_contact')
        .submit(function(e) {
            e.preventDefault();
        })
        .validate({
            rules: {
                contact_id: {
                    remote: {
                        url: '/contacts/check-contacts-id',
                        type: 'post',
                        data: {
                            contact_id: function() {
                                return $('#contact_id').val();
                            },
                            hidden_id: function() {
                                if ($('#hidden_id').length) {
                                    return $('#hidden_id').val();
                                } else {
                                    return '';
                                }
                            },
                        },
                    },
                },
            },
            messages: {
                contact_id: {
                    remote: LANG.contact_id_already_exists,
                },
            },
            submitHandler: function(form) {
                var data = $(form).serialize();
                $.ajax({
                    method: 'POST',
                    url: $(form).attr('action'),
                    dataType: 'json',
                    data: data,
                    beforeSend: function(xhr) {
                        __disable_submit_button($(form).find('button[type="submit"]'));
                    },
                    success: function(result) {
                        if (result.success == true) {
                            $('select#contact_id').append(
                                $('<option>', { value: result.data.id, text: result.data.name })
                            );
                            $('select#contact_id')
                                .val(result.data.id)
                                .trigger('change');
                            $('div.contact_modal').modal('hide');
                            update_shipping_address(result.data)
                            toastr.success(result.msg);
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            },
        });
    $('.contact_modal').on('hidden.bs.modal', function() {
        $('form#quick_add_contact')
            .find('button[type="submit"]')
            .removeAttr('disabled');
        $('form#quick_add_contact')[0].reset();
    });

</script>
@endsection