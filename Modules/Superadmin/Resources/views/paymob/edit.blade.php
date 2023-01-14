<!-- Modal -->
<div class="modal-dialog" role="document">
    <div class="modal-content">
     {!! Form::open(['url' => action('\Modules\Superadmin\Http\Controllers\PaymobController@update',$tp->id), 'method' => 'PUT', 'id' => '']) !!}

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">حالة الصرف</h4>
      </div>

      <div class="modal-body">
             <div class="form-group">
                {!! Form::label('payout_status', __( "حالة الصرف")) !!}

                {!! Form::select('payout_status', $status, $tp->payout_status, ['class' => 'form-control']); !!}
              </div>

            
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">@lang( "superadmin::lang.update")</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">الغاء</button>
      </div>
      {!! Form::close() !!}
    </div>
</div>