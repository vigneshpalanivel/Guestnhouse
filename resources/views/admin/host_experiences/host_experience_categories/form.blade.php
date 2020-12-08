<div class="form-group status">
  	<label for="input_status" class="col-sm-3 control-label">Name<em class="text-danger">*</em></label>
  	<div class="col-sm-6">
    	{!! Form::text('name', @$host_experience_category->name, ['class' => 'form-control', 'id' => 'input_name', 'placeholder' => 'Name']) !!}                    
  		<span class="text-danger">{{ $errors->first('name') }}</span>
  	</div>
</div>
<div class="form-group status">
    <label for="input_status" class="col-sm-3 control-label">Image<em class="text-danger">*</em></label>
    <div class="col-sm-6">
      {!! Form::file('image', ['class' => 'form-control', 'id' => 'input_image']) !!}      
      <span class="text-info">Note: Preferred image dimenstions are 320X500</span>
      <span class="text-danger">{{ $errors->first('image') }}</span>
      @if(@$host_experience_category->image)
        <br><br>
        <img src="{{@$host_experience_category->image_url}}" height="100" width="200">
      @endif
    </div>
</div>
<div class="form-group status">
  	<label for="input_status" class="col-sm-3 control-label">Status<em class="text-danger">*</em></label>
  	<div class="col-sm-6">
    	{!! Form::select('status', array('Active' => 'Active', 'Inactive' => 'Inactive'), @$host_experience_category->status, ['class' => 'form-control', 'id' => 'input_status', 'placeholder' => 'Select']) !!}                    
  		<span class="text-danger">{{ $errors->first('status') }}</span>
  	</div>
</div>