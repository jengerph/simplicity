next_tab();

$.removeCookie('jQu3ry_5teps_St@te_example-vertical');

function next_tab(){

  var wholesaler_id = "{WHOLESALER_ID}";

  var form = $("#addplan");
  $("#example-vertical").steps({
      headerTag: "h3",
      bodyTag: "section",
      transitionEffect: "slideLeft",
      stepsOrientation: "vertical",
      saveState: true,
      startIndex: 0,
      onStepChanging: function (event, currentIndex, newIndex)
      { console.log(currentIndex);
              if( $("#service_type").val() == 0 ) {
                if ( currentIndex > newIndex ) {
                  return true;
                }
              } else if ( currentIndex == 1 && $("#parent_plan_id").val() == 0 ) {

                if ( currentIndex > newIndex ) {
                  return true;
                }

              } else if ( currentIndex == 2 && ( !$("#description").val() || !$("#monthly_cost").val() || !$("#early_termination_cost").val() || !$("#extra_data_cost").val() ) ) {

                if ( currentIndex > newIndex ) {
                  return true;
                }

              } else if ( $("#service_type").val() != 5 && currentIndex == 3 && ( $("#extra_type").is(":checked") == false || !$("#extra_desc").val() || !$("#extra_month_cost").val() || !$("#extra_setup_cost").val() ) ) {

                if ( currentIndex > newIndex ) {
                  return true;
                }

              } else if ( $("#service_type").val() == 5 && currentIndex == 3 && ( !$("#setup_fee").val() || !$("#monthly_fee").val() || !$("#standard_capf").val() || !$("#priority_capf").val() ) ) {

                if ( currentIndex > newIndex ) {
                  return true;
                }

              } else {
                   console.log($("#extra_type").is(":checked"));
                return true;
              }
      },
      onFinishing: function (event, currentIndex)
      {
        if ( $("#service_type").val() != 5 ) {
            if ( $("input[type='checkbox'][name='extra_type[]']").is(":checked") == false ) {
            return false;
            } else {
              return true;
            }
        } else {
          if ( $("#service_type").val() == 5 && currentIndex == 1 && ( !$("#setup_fee").val() || !$("#monthly_fee").val() || !$("#standard_capf").val() || !$("#priority_capf").val() ) ) {
            return false;
          } else {
            return true;
          }
        }
      },
      onFinished: function (event, currentIndex)
      {
                  $.removeCookie('jQu3ry_5teps_St@te_example-vertical');
                  document.getElementById("addplan").submit2.click();
                  return true;
      },
      labels: {
        cancel: "Cancel",
        current: "current step:",
        pagination: "Pagination",
        finish: "Create Plan",
        next: "Next",
        previous: "Previous",
        loading: "Loading ..."
    }
  });
  
}