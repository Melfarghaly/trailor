@extends('layouts.app')
@section('title', __( 'measurements.measurements' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'measurements.measurementss' )
        <small>@lang( 'measurements.manage_your_measurementss' )</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'measurements.all_your_measurementss' )])
        @can('measurements.create')
            @slot('tool')
                <div class="box-tools">
                    <button type="button" class="btn btn-block btn-primary" 
                            data-toggle="modal"
                            data-target=".measurements_modal">
                            <i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
                </div>
            @endslot
        @endcan
       
            <div class="table-responsive">
                <table class="table">
                    <thead>
                      <tr>
                        <th scope="col">#</th>
                        <th scope="col">اسم الخاصية</th>
                        <th scope="col">النوع</th>
                        <th scope="col">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($measurements as $measurement)
                      <tr>
                        <th scope="row">{{  $loop->iteration}}</th>
                        <td>{{ $measurement->label }}</td>
                        <td>{{ $measurement->type }}</td>
                        <td>
                          <a href="{{ route('measurements.edit', $measurement->id) }}" class="btn btn-xs  btn-sm btn-primary">Edit</a>
                          <form action="{{ route('measurements.destroy', $measurement->id) }}" method="POST" class="d-inline-block">
                            @method('DELETE')
                            @csrf
                            <button type="submit" class="btn btn-sm btn-xs btn-danger">Delete</button>
                          </form>
                        </td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
            </div>
      
    @endcomponent

    
    <div class="modal fade measurements_modal" tabindex="-1" role="dialog"  style="padding: 20px"
    	aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content" style="padding: 20px ">
                <div class="row">
                    <div class="col-md-12">
                        {!! Form::open(['url' => action('MeasurementController@store'), 'method' => 'post', 'files'=>true,'id' => 'dmeasurements_add_form' ]) !!}
                            <div class="form-group">
                                <label for="key">Key</label>
                                <input type="text" class="form-control" id="key" name="key" placeholder="Enter key">
                            </div>
                            <div class="form-group">
                                <label for="type">Type</label>
                                <select class="form-control" id="type" name="type">
                                    <option value="select">خيار من متعدد</option>
                                    <option value="text">نص او رقم</option>
                                </select>
                            </div>
                            <div class="todolist">
                                <div class="form-group">
                                    <label for="options">الخيارات اسم او صورة</label>
                                        <div class="options-wrapper">
                                            <div class="row">
                                                <div class="option-item col-md-5">
                                                    <input type="text" class="form-control option-name" name="options[][name]" placeholder="Option Name">
                                                </div>  
                                                <div class=" col-md-5">
                                                    <input type="file" class="form-control option-image"  name="options[][image]">
                                                </div>
                                                <div class="col-md-2">
                                                    <button class="btn btn-success add-option">+ </button>
                                                </div>
                                            </div>
                                    
                                        </div>
                                    </div>
                                </div>
                               
                            </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
                        </div>
                    
                        {!! Form::close() !!}
                    </div>
                </div>
                
            
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
       
    </div>
    <div class="modal fade tax_group_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->
@section('javascript')
<script>
$(document).ready(function() {
    $(".add-option").click(function(event) {
        event.preventDefault();
        var newOption = $(
            '<div class="row">'+
                '<div class="option-item col-md-5">' +
                    '<input type="text" class="form-control option-name"  name="options[][name]" placeholder="Option Name">' +
                '</div>' +
                '<div class="col-md-5">' +
                    '<input type="file" class="form-control option-image"  name="options[][image]">' +
                '</div>' +
                '<div class="col-md-2">' +
                    '<button class="btn btn-danger remove-option">X</button>' +
                '</div>'+
            '</div>'
        );
        $(".options-wrapper").append(newOption);
    });

    $(".options-wrapper").on("click", ".remove-option", function() {
        $(this).closest(".row").remove(); 
    });
});
$("#type").change(function(){
    debugger;
    if($(this).val() == 'text'){
        $(".todolist").hide();
    }else{
        $(".todolist").show();
    }
});
</script>
@endsection
@endsection
