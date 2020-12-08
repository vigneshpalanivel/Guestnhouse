@extends('admin.template')

@section('main')
<style type="text/css">
 [ng-cloak] {
    display: none !important;
  }
</style>
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper" ng-controller="page" ng-cloak>
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Edit Page
      </h1>
      <ol class="breadcrumb">
        <li><a href="../dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="../pages">Page</a></li>
        <li class="active">Edit</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <!-- right column -->
        <div class="col-md-12">
          <!-- Horizontal Form -->
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Edit Page Form</h3>
            </div>
            <!-- /.box-header -->
            <!-- form start -->
              {!! Form::open(['url' => ADMIN_URL.'/edit_page/'.$result->id, 'class' => 'form-horizontal']) !!}
              <div class="box-body">
              <span class="text-danger">(*)Fields are Mandatory</span>
                <div class="form-group">
                  <label for="input_language" class="col-sm-3 control-label">Language<em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    {!! Form::select('language', $languages, 'en', ['class' => 'form-control', 'id' => 'input_language', 'disabled' =>'disabled']) !!}
                  </div>
                </div>
                <div class="form-group">
                  <label for="input_name" class="col-sm-3 control-label">Name<em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    {!! Form::text('name', $result->name, ['class' => 'form-control', 'id' => 'input_name', 'placeholder' => 'Name']) !!}
                    <span class="text-danger">{{ $errors->first('name') }}</span>
                  </div>
                </div>
                <div class="form-group">
                  <label for="input_content" class="col-sm-3 control-label">Content<em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    <textarea id="txtEditor" name="txtEditor"></textarea>
                    {!! Form::textarea('content', $result->content, ['id' => 'content', 'hidden' => 'true']) !!}
                    <span class="text-danger">{{ $errors->first('content') }}</span>
                  </div>
                </div>
                <div class="form-group">
                  <label for="input_footer" class="col-sm-3 control-label">Footer<em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    {!! Form::select('footer', array('yes' => 'Yes', 'no' => 'No'), $result->footer, ['class' => 'form-control', 'id' => 'input_footer', 'placeholder' => 'Select']) !!}
                    <span class="text-danger">{{ $errors->first('footer') }}</span>
                  </div>
                </div>
                <div class="form-group input_under" style="display: {{old('footer',$result->footer)=='yes'?'block':'none'}};">
                  <label for="input_under" class="col-sm-3 control-label">Under<em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    {!! Form::select('under', array('company' => 'Company', 'discover' => 'Discover', 'hosting' => 'Hosting'), $result->under, ['class' => 'form-control', 'id' => 'input_under', 'placeholder' => 'Select']) !!}
                    <span class="text-danger">{{ $errors->first('under') }}</span>
                  </div>
                </div>
                <div class="form-group">
                  <label for="input_status" class="col-sm-3 control-label">Status<em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    {!! Form::select('status', array('Active' => 'Active', 'Inactive' => 'Inactive'), $result->status, ['class' => 'form-control', 'id' => 'input_status', 'placeholder' => 'Select']) !!}
                    <span class="text-danger">{{ $errors->first('status') }}</span>
                    <em class="text-danger status_error" style="font-size: 14px;" hidden="hidden" >Must choose Active(Atleast One Static Page in 'Active')</em>
                  </div>
                </div>
                
                <div class="panel" ng-init="translations = {{json_encode(old('translations') ?: $result->translations)}}; removed_translations =  []; errors = {{json_encode($errors->getMessages())}}; result_translations = {{json_encode($result->translations)}}">
                  <div class="panel-header">
                    <h4 class="box-title text-center">Translations</h4>
                  </div>
                  <div class="panel-body" ng-init="languages = {{json_encode($languages)}}">
                    <input type="hidden" name="removed_translations" ng-value="removed_translations.toString()">
                    <div class="row" ng-repeat="translation in translations">
                      <input type="hidden" name="translations[@{{$index}}][id]" value="@{{translation.id}}">
                      <div class="form-group">
                        <label for="input_language_@{{$index}}" class="col-sm-3 control-label">Language<em class="text-danger">*</em></label>
                        <div class="col-sm-6">
                          <select name="translations[@{{$index}}][locale]" class="form-control " id="input_language_@{{$index}}" ng-model="translation.locale" >
                            <option value="" ng-if="translation.locale == ''">Select Language</option>
                            <option ng-if="!languages.hasOwnProperty(translation.locale) && translation.locale != '';" value="@{{translation.locale}}" >@{{translation.language.name}} </option>
                            @foreach($languages as $key => $value)
                              <option value="{{$key}}" ng-if="(('{{$key}}' | checkKeyValueUsedInStack : 'locale': translations) || '{{$key}}' == translation.locale) && '{{$key}}' != 'en'">{{$value}}</option>
                            @endforeach
                          </select>
                          <span class="text-danger ">@{{ errors['translations.'+$index+'.locale'][0] }}</span>
                        </div>
                        <div class="col-sm-1">
                          <button class="btn btn-danger btn-xs" ng-click="translations.splice($index, 1); removed_translations.push(translation.id)">
                            <i class="fa fa-trash"></i>
                          </button>
                        </div>
                      </div>

                      <div class="form-group">
                        <label for="input_name_@{{$index}}" class="col-sm-3 control-label">Name<em class="text-danger">*</em></label>
                        <div class="col-sm-6">
                          {!! Form::text('translations[@{{$index}}][name]', '@{{translation.name}}', ['class' => 'form-control ', 'id' => 'input_name_@{{$index}}', 'placeholder' => 'Name']) !!}
                          <span class="text-danger ">@{{ errors['translations.'+$index+'.name'][0] }}</span>
                        </div>
                      </div>

                      <div class="form-group"  ng-init="multiple_editors($index)">
                        <label for="input_content_@{{$index}}" class="col-sm-3 control-label">Content<em class="text-danger">*</em></label>
                        <div class="col-sm-6">
                          <textarea class="editors" id="editor_@{{$index}}" name="translations[@{{$index}}][txtEditor]" data-index="@{{$index}}"></textarea>
                          <textarea class="contents " id="content_@{{$index}}" name="translations[@{{$index}}][content]" hidden="true">@{{result_translations[$index].content}}</textarea>
                          {{--{!! Form::textarea('translations[@{{$index}}][content]', '@{{translation.content}}', ['class' => 'form-control', 'id' => 'input_content_@{{$index}}', 'placeholder' => 'Content', 'hidden' => true]) !!}--}}
                          <span class="text-danger ">@{{ errors['translations.'+$index+'.content'][0] }}</span>
                        </div>
                      </div>

                      <legend ng-if="$index+1 < translations.length"></legend>
                    </div>
                  </div>
                  <div class="panel-footer">
                    <div class="row" ng-show="translations.length <  {{count($languages) - 1}}">
                      <div class="col-sm-12">
                        <button type="button" class="btn btn-info" ng-click="translations.push({locale:''});" >
                          <i class="fa fa-plus"></i> Add Translation
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <button type="submit" class="btn btn-info pull-right" id="edt_btn" name="submit" value="submit">Submit</button>
                 <button type="submit" class="btn btn-default pull-left" name="cancel" value="cancel">Cancel</button>
              </div>
              <!-- /.box-footer -->
            {!! Form::close() !!}
          </div>
          <!-- /.box -->
        </div>
        <!--/.col (right) -->
      </div>
      <!-- /.row -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
@stop

@push('scripts')
<script type="text/javascript">
$("#txtEditor").Editor(); 
$('.Editor-editor').html($('#content').val());
if("{{$result->under}}"=="company")
$('#input_status').change(function(){
        $('#edt_btn').prop('disabled', true);
        var status_id="{{ $result->id }}";
        var status_txt=$(this).val();
        if(status_txt == 'Inactive'){
        $.ajax({
          type: "post",
          url: '{{ url("/") }}/'+ADMIN_URL+'/page_status_check/'+status_id,
          success:function(data){
            if (data == 'InActive') {
               $('.status_error').show();
               $('#edt_btn').prop('disabled', true);
            }
            else
            {
              $('.status_error').hide();
               $('#edt_btn').prop('disabled', false);

            }
          },
      }); 
        }
        else
        {
           $('.status_error').hide();
               $('#edt_btn').prop('disabled', false);
        }

    });

  $(document).ready(function(){
    $('#input_footer').change(function(){
      if($(this).val()=='yes'){
        $('.input_under').show()
      }else{
        $('.input_under').hide()
      }
    })
  })
</script>
@endpush