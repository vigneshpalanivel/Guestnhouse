<div class="main-wrap guest-require-wrap bg-white">
  <div class="save-info">
    @include('host_experiences.manage_experience.header', ['header_inverse' => true])
  </div>
  <div class="col-12 col-lg-7 main-wrap-info mt-lg-4">
    <h3>
      {{trans('experiences.manage.who_can_attend_the_experience')}}
    </h3>
    <p>
      {{trans('experiences.manage.guest_requirement_page_desc')}}
    </p>

    <div class="guest-require-list my-4">
      <h4>
        {{trans('experiences.manage.alcohol')}}
      </h4>

      <label class="verify-check" for="check1">
        <input type="checkbox" name="guest_requirements[includes_alcohol]" id="guest_requirements_includes_alcohol" class="chekbox1" ng-model="host_experience.guest_requirements.includes_alcohol" ng-true-value="'Yes'" ng-false-value="'No'">     
        {{trans('experiences.manage.includes_alcohol_legal_drinking_age_only')}}
      </label>
    </div>

    <div class="guest-require-list my-4">
      <h4>
        {{trans('experiences.manage.minimum_age')}}
      </h4>
      <small>
        {{trans('experiences.manage.set_age_limit_for_guests_minors_with_gaurdians')}}
      </small>

      <div class="row mt-2">
        <div class="col-lg-8">
          <div class="select">
            <i class="icon-chevron-down"></i>
            <select name="host_experience[minimum_age]" id="guest_requirements_minimum_age" ng-model="host_experience.guest_requirements.minimum_age">
              <option ng-if="host_experience.guest_requirements.minimum_age == ''" value="">
                {{trans('experiences.manage.select_minimum_age')}}
              </option>
              @foreach($host_experience->minimum_age_array as $age)
              <option value="{{$age}}">
                {{$age}}
              </option>
              @endforeach
            </select>   
          </div>
          <p class="text-danger" ng-show="form_errors.guest_requirements.minimum_age.length">
            @{{form_errors.guest_requirements.minimum_age[0]}}
          </p>  
        </div>
      </div>
    </div>

    <div class="guest-require-list my-4">      
      <label class="verify-check" for="check2">
        <input type="checkbox" class="chekbox1" name="guest_requirements[allowed_under_2]" id="guest_requirements_allowed_under_2" ng-model="host_experience.guest_requirements.allowed_under_2" ng-true-value="'Yes'" ng-false-value="'No'">
        {{trans('experiences.manage.parents_can_bring_under_2')}}
      </label>
    </div>

    <div class="guest-require-list my-4">
      <h4>
        {{trans('experiences.manage.special_certifications')}}
      </h4>       
      <textarea placeholder="{{trans('experiences.manage.special_certifications_example')}}" rows="3" name="guest_requirements[special_certifications]" id="guest_requirements_special_certifications" ng-model="host_experience.guest_requirements.special_certifications">
      </textarea> 
    </div>

    <div class="guest-require-list my-4"> 
      <h4>
        {{trans('experiences.manage.additional_requirements')}}
      </h4>       
      <textarea placeholder="{{trans('experiences.manage.additional_requirements_example')}}" rows="3" name="guest_requirements[additional_requirements]" id="guest_requirements_additional_requirements" ng-model="host_experience.guest_requirements.additional_requirements">
      </textarea>   
    </div>

    <div class="d-none">
      <p>
        Verified ID
      </p>
      <small>
        The primary booker has to successfully complete verified ID in order for them and their guests to attend your experience.
      </small>

      <div class="my-4">
        <label class="verify-check" for="check3">
          <input type="checkbox" name="" id="check3" class="chekbox1"> 
          Require the primary booker to 
          <a href="javascript:void(0)"> 
            verify their ID.
          </a>
        </label>
      </div>
    </div>
    <div class="mt-4 mt-md-5">
      @include('host_experiences.manage_experience.control_buttons')
    </div>
  </div>
  <div class="d-none d-lg-block main-wrap-img">
    @include('host_experiences/manage_experience/mobile_preview')
  </div>
</div>
<!--  main_bar end -->