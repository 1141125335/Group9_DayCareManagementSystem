function convertImageToBase64(data, callback)
{
  image = $(data).prop('files')[0];
  var validImageTypes = ["image/jpeg", "image/png"];

  if($.inArray(image.type, validImageTypes) < 0)
  {
    callback('false');
    return;
  }

  var FR = new FileReader();
  FR.onload = function(e)
  {
     callback(e.target.result);
  };
  FR.readAsDataURL(image);
}

function checkisemail(email) 
{
  var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  return regex.test(email);
}

function checkPasswordValid(password)
{
  var regex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/;

  return regex.test(password);
}

function simp_ajax(type, data, url, async=true)
{
  return $.ajax({
        cache:      false,
        url:        url,
        dataType:   "json",
        type:       type,
        data:       data,
        async:    async,
    });      
}

function data_ajax(type, data, url, async=true)
{

 return $.ajax({  
     url: url,  
     type: type,  
     data: data,  
     dataType:   "json",
     cache: false,
     processData: false,  
     contentType: false, 
     context: this,
  });
}

function changePermission()
{
  var page = document.location.href.match(/[^\/]+$/)[0];

  simp_ajax('POST', 'action=changePermission', page).done(function(r)
  {
    window.location.reload();
  });
}