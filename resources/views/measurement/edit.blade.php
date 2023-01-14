@extends('layouts.app')
@section('title', __( 'measurements.measurements' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'measurements.measurements' )
        <small>@lang( 'measurements.manage_your_measurements' )</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'measurements.all_your_measurements' )])
      
        <div class="col-md-6">
            {!! Form::open(['url' => action('MeasurementController@update',$measurement->id), 'method' => 'post', 'id' => 'measurements_add_form' ]) !!}
                <div class="form-group">
                    <label for="key">Key</label>
                    <input type="text" class="form-control" id="key" name="key" placeholder="Enter key">
                </div>
                <div class="form-group">
                    <label for="type">Type</label>
                    <select class="form-control" id="type" name="type">
                    <option value="select">Select</option>
                    <option value="text">Text</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
                    <a class="btn btn-default" href="/measurements">@lang( 'back' )</a>
                </div>
    
            {!! Form::close() !!}
        </div>
    @endcomponent
</section>
@endsection