
$(function () {
    $('[data-toggle="tooltip"]').tooltip()
})
$(function () {
    $('[data-toggle="popover"]').popover()
}) 

$(document).on('change', 'input[type=radio][name^=tagRadio]', function() {
    $(this).closest('td').find('button').text(this.value);
});

// remove xml information
$(document).on('click', '.removeXml', function() {
    let data_id = $(this).attr("data-id");
    let remove_name = $(this).closest('tr').find('.sorting_1').text();
    $("#removeId").val(data_id);
    $("#removeName").val(remove_name);
})

// running xml information
$(document).on('click', '.runningXml', function() {
    let data_id = $(this).attr("data-id");
    $("#runningId").val(data_id);
});

// utm value gathering
$(document).on('click', '.createUtm', function() {
    let utm_final_value = "";
    $("#createUtmModal input").each(function(){
        if( !$(this).val() ) {
              
        }
        else {
            if($(this).attr('id') == 'custom_field'){
                utm_final_value += `${$(this).val()}&`;
            }
            else {
                utm_final_value += `${$(this).attr('id')}=${$(this).val()}&`;
            }
        }
        console.log(utm_final_value);
    });
    utm_final_value = utm_final_value.slice(0,-1);
    console.log(utm_final_value);
    $("#utm_final_value_create").val(utm_final_value);
    $('#createUtmModal').modal('hide');
});

//remove modal handling
$(".removeItem").click(function(){
    let data_id = $("#removeId").val();
    let remove_name = $("#removeName").val();
    $.ajax({
        url: "parsexml.php",
        type: "post",
        dataType: "json",
        data: {"removeItem": "valid", "data_id": data_id, "remove_name": remove_name},
        success: function(result) {
            console.log(result);
            if(result) {
                location.reload();
            }
        }
    })
})

// running modal handling
$(".runningItem").click(function(){
    let data_id = $("#runningId").val();
    $.ajax({
        url: "parsexml.php",
        type: "post",
        dataType: "json",
        data: {"runningItem": "valid", "data_id": data_id},
        success: function(result) {
            console.log(result);
            if(result.data == "true") {
                $('#runningModal').modal('toggle');
                $("#confirmModal").modal('toggle');
            }
            else if(result.data == "warning") {
                alert('This is already in progress');
            }
            else {
                alert("Something went wrong while processing");
            }
        }
    })
})

// Confirm modal handling
$(".confirmItem").click(function() {
    location.reload();
});

// update xml 
$("#updateDetail").click(function(){
    let willAddCountry = "invalid";
    if ($('#willCountryCheck').is(":checked"))
    {
        willAddCountry = $('#willEditCountry').val();
    }
    let willAddIndustry = "invalid";
    if ($('#willIndustryCheck').is(":checked"))
    {
        willAddIndustry = $('#willEditIndustry').val();
    }
    let willAddCompany = "invalid";
    if ($('#willCompanyCheck').is(":checked"))
    {
        willAddCompany = $('#willEditCompany').val();
    }
    let jobLocationType = "invalid";
    if ($('#willLocationCheck').is(":checked"))
    {
        jobLocationType = $('#jobLocationType').val();
    }
    let id = $("#xmlid").val();
    let feedName = $("#feedName").val();
    let feedOrigin = $("#feedOrigin").val();
    let xmlurl = $("#xmlurl").val();
    let updatetag = "";
    $( "tbody#parsing input[type=radio][name^=tagRadio]:checked" ).each(function( index ) {
        updatetag += `${this.value},`;
    });
    let utmValue = $("#utm_final_value_create").val();
    if(feedName == "" || xmlurl == "" || willAddCountry == "" || willAddIndustry == "") {
        alert("Fill form values");
    }
    else {
        $.ajax({
            url: "parsexml.php",
            type: "post",
            dataType: "json",
            data: {"updateFeed": "valid", 
                    "id": id, 
                    "feedName": feedName, 
                    "feedOrigin": feedOrigin, 
                    "xmlurl": xmlurl, 
                    "updatetag": updatetag, 
                    "willAddCountry": willAddCountry, 
                    "willAddIndustry": willAddIndustry, 
                    "willAddCompany": willAddCompany, 
                    "jobLocationType": jobLocationType,
                    "utmValue": utmValue,
                },
            success: function(result) {
                console.log(result);
                if(result.data == "true") {
                    window.location.href = 'managefeeds.php';
                }
                else {
                    alert('Something went wrong while saving feed details');
                }
            }
        })
    }
})

$("#saveDetail").click(function(){
    
    let willAddCountry = "invalid";
    if ($('#willCountryCheck').is(":checked"))
    {
        willAddCountry = $('#willAddCountry').val();
    }
    let willAddIndustry = "invalid";
    if ($('#willIndustryCheck').is(":checked"))
    {
        willAddIndustry = $('#willAddIndustry').val();
    }
    let willAddCompany = "invalid";
    if ($('#willCompanyCheck').is(":checked"))
    {
        willAddCompany = $('#willAddCompany').val();
    }
    let jobLocationType = "invalid";
    if ($('#willLocationCheck').is(":checked"))
    {
        jobLocationType = $('#jobLocationType').val();
    }
    let feedName = $('#feedName').val();
    let xmlurl = $('#xmlurlHidden').val();
    let basetag = $("#baseTagValue").val();
    let updatetag = "";
    let cdatatag = $("#cdataTag").val();
    let isChild = $("#isChild").val();
    let utmValue = $("#utm_final_value_create").val();
    $( "tbody#parsing input[type=radio][name^=tagRadio]:checked" ).each(function( index ) {
        updatetag += `${this.value},`;
    });
    if(feedName == "" || xmlurl == "" || willAddCountry == "" || willAddIndustry == "" || willAddCompany == "" ) {
        alert("Fill form values");
    }
    else {
        $.ajax({
            url: "parsexml.php",
            type: "post",
            dataType: "json",
            data: {"saveFeed": "valid", 
                    "feedName": feedName, 
                    "xmlurl": xmlurl, 
                    "basetag": basetag, 
                    "updatetag": updatetag, 
                    "cdatatag": cdatatag, 
                    "willAddCountry": willAddCountry, 
                    "willAddIndustry": willAddIndustry, 
                    "willAddCompany" : willAddCompany,
                    "jobLocationType": jobLocationType, 
                    "isChild": isChild,
                    "utmValue": utmValue,
                    },
            success: function(result) {
                if(result.data == "true") {
                    window.location.href = 'managefeeds.php';
                }
                else if(result.data == "duplicate") {
                    alert('Name or URL is already stored');
                }
                else {
                    alert('Something went wrong while saving feed details');
                }
            }
        })
    }
})


//add country pre tag
$("#willCountryCheck").click(function(){
    $("#willAddCountry").toggle(200);
})

//add industry pre tag
$("#willIndustryCheck").click(function(){
    $("#willAddIndustry").toggle(200);
})

//add company pre tag
$("#willCompanyCheck").click(function(){
    $("#willAddCompany").toggle(200);
})

//update country pre tag
$("#willCountryCheck").click(function(){
    $("#willEditCountry").toggle(200);
})

//update industry pre tag
$("#willIndustryCheck").click(function(){
    $("#willEditIndustry").toggle(200);
})

//update company pre tag
$("#willCompanyCheck").click(function(){
    $("#willEditCompany").toggle(200);
})

//add jobLocation tag
$("#willLocationCheck").click(function(){
    $("#jobLocationType").toggle(200);
})

//download file xml and extract it
$("#downloadxml").click(function(){
    let xmlurl = $("#xmlurl").val();
    // if(xmlurl.indexOf(".zip") != -1 || xmlurl.indexOf(".gz") != -1){
        if(xmlurl == "") {
            alert("Please enter the xmlURL");
        }
        else {
            $(".filexmlarea").LoadingOverlay("show", {
                background  : "rgba(165, 190, 100, 0.5)"
            });
            $.ajax({
                url: "parsexml.php",
                type: "post",
                dataType: "json",
                data: {"downloadfile": "valid", "xmlurl": xmlurl},
                success: function(result) {
                    $(".filexmlarea").LoadingOverlay("hide", true);
                    if(result.data == "true") {
                        $("#confirmModal").modal('toggle');
                    }
                    else if(result.data == "warning") {
                        alert('This is already in progress');
                    }
                    else {
                        alert("Something went wrong while processing");
                    }
                }
            })
        }
    // }
    // else {
    //     alert("Please check the xml url is for zip or gz");
    // }
})

// remove xml file information in modal
$(".removeFileInfo").click(function(){
    let data_id = $("#removeId").val();
    $.ajax({
        url: "parsexml.php",
        type: "post",
        dataType: "json",
        data: {"removeFile": "valid", "data_id": data_id},
        success: function(result) {
            console.log(result);
            if(result) {
                location.reload();
            }
        }
    })
})

// remove xml file information
$(document).on('click', '.removeFile', function() {
    let data_id = $(this).attr("date-id");
    $("#removeId").val(data_id);
})

$(document).ready(function() {
    $('#feedinfo').DataTable({
         "aoColumns": [
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            { "bSortable": false }
         ],
         "pageLength": 50,
    });
} );


$(document).ready(function() {
    $('#fileinfo').DataTable({
        "aoColumns": [
            null,
            null,
            null,
            { "bSortable": false }
         ],
    });
} );

$("#parseXML").click(function(){

    let xmlurl = $("#xmlurl").val()
    if(xmlurl == '')  {
        alert("Please enter the xmlURL");
    }

    else {
        $(".container-fluid").LoadingOverlay("show", {
            background  : "rgba(165, 190, 100, 0.5)"
        });
        $.ajax({
            url:"parsexml.php",
            type: "post",
            dataType: 'json',
            data: {parse: "valid", url: xmlurl},
            success:function(result){
                $('#parsing').html("");

                $(".container-fluid").LoadingOverlay("hide", true);
                if(result == false) {
                    alert("Please Check the job xml url");
                }
                if(result == "error") {
                    alert("Please Check the job xml url");
                }
                if(result.data == "false") {
                    alert("This is Zip and gz file url, Please go 'Add file feed' and download file first.");
                }
                else {
                    let isChild = result.is_child;
                    let mainString = '';
                    let baseTag = result.baseTag;
                    let baseTagValue = '';
                    let baseValue = result.baseValue;
                    let cdataTag = result.cdataTag;
                    let cdatTagValue = '';
                    for(i = 0; i < cdataTag.length; i++) {
                        cdatTagValue += `${cdataTag[i]},`;
                    }
                    for(i = 0; i < baseTag.length; i++) {
                        baseTagValue += `${baseTag[i]},`;
                    }
                    $("#xmlurlHidden").val(xmlurl);
                    $("#cdataTag").val(cdatTagValue);
                    $("#baseTagValue").val(baseTagValue);
                    $("#isChild").val(isChild);
                    for(i = 0; i < baseTag.length; i++) {
                        mainString += `
                        <tr>
                            <td class="align-middle"><strong> &lt;${baseTag[i]}&gt;</strong></td>
                            <td class="align-middle text" data-container="body" data-toggle="popover" data-placement="top" data-content="rices."><span><i class="fas fa-eye"></i>${baseValue[i]}</span></td>
                            <td align="right" class="" style="width: 15%;">
                                <div class="dropdown">
                                    <button class="btn btn-default  dropdown-toggle btn-block" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Default
                                    </button>
                                    <div class="dropdown-menu dropdown-status-tag dropdown-menu-right p-4" aria-labelledby="dropdownMenuButton">
                                        <div class="row mx-sm-n1">
                                            <div class="col-md">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tagRadio_${i}" id="labelRadio_${i}_1" value="Default" checked>
                                                    <label class="form-check-label" for="labelRadio_${i}_1">
                                                    &lt;Default&gt;
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tagRadio_${i}" id="labelRadio_${i}_2" value="id">
                                                    <label class="form-check-label" for="labelRadio_${i}_2">
                                                    &lt;id&gt;
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tagRadio_${i}" id="labelRadio_${i}_3" value="title">
                                                    <label class="form-check-label" for="labelRadio_${i}_3">
                                                    &lt;title&gt;
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tagRadio_${i}" id="labelRadio_${i}_4" value="company">
                                                    <label class="form-check-label" for="labelRadio_${i}_4">
                                                    &lt;company&gt;
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tagRadio_${i}" id="labelRadio_${i}_5" value="addressCountry">
                                                    <label class="form-check-label" for="labelRadio_${i}_5">
                                                    &lt;addressCountry&gt;
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tagRadio_${i}" id="labelRadio_${i}_6" value="city">
                                                    <label class="form-check-label" for="labelRadio_${i}_6">
                                                    &lt;city&gt;
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tagRadio_${i}" id="labelRadio_${i}_60" value="CPC">
                                                    <label class="form-check-label" for="labelRadio_${i}_60">
                                                    &lt;CPC&gt;
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md">                                            
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tagRadio_${i}" id="labelRadio_${i}_26" value="addressRegion">
                                                    <label class="form-check-label" for="labelRadio_${i}_26">
                                                    &lt;addressRegion&gt;
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tagRadio_${i}" id="labelRadio_${i}_7" value="geonameId">
                                                    <label class="form-check-label" for="labelRadio_${i}_7">
                                                    &lt;geonameId&gt;
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tagRadio_${i}" id="labelRadio_${i}_8" value="geonameLocality">
                                                    <label class="form-check-label" for="labelRadio_${i}_8">
                                                    &lt;geonameLocality&gt;
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tagRadio_${i}" id="labelRadio_${i}_9" value="geonameLongitude">
                                                    <label class="form-check-label" for="labelRadio_${i}_9">
                                                    &lt;geonameLongitude&gt;
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tagRadio_${i}" id="labelRadio_${i}_10" value="geonameLatitude">
                                                    <label class="form-check-label" for="labelRadio_${i}_10">
                                                    &lt;geonameLatitude&gt;
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tagRadio_${i}" id="labelRadio_${i}_11"  value="content">
                                                    <label class="form-check-label" for="labelRadio_${i}_11">
                                                    &lt;content&gt;
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tagRadio_${i}" id="labelRadio_${i}_110" value="salaryMin">
                                                    <label class="form-check-label" for="labelRadio_${i}_110">
                                                    &lt;salaryMin&gt;
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tagRadio_${i}" id="labelRadio_${i}_14" value="url">
                                                    <label class="form-check-label" for="labelRadio_${i}_14">
                                                    &lt;url&gt;
                                                    </label>
                                                </div>                                                
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tagRadio_${i}" id="labelRadio_${i}_12" value="datePosted">
                                                    <label class="form-check-label" for="labelRadio_${i}_12">
                                                    &lt;datePosted&gt;
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tagRadio_${i}" id="labelRadio_${i}_16" value="remotePolicy">
                                                    <label class="form-check-label" for="labelRadio_${i}_16">
                                                    &lt;remotePolicy&gt;
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tagRadio_${i}" id="labelRadio_${i}_17" value="employmentType">
                                                    <label class="form-check-label" for="labelRadio_${i}_17">
                                                    &lt;employmentType&gt;
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tagRadio_${i}" id="labelRadio_${i}_18" value="salaryCurrency">
                                                    <label class="form-check-label" for="labelRadio_${i}_18">
                                                    &lt;salaryCurrency&gt;
                                                    </label>
                                                </div>                                                
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tagRadio_${i}" id="labelRadio_${i}_19" value="industry">
                                                    <label class="form-check-label" for="labelRadio_${i}_19">
                                                    &lt;industry&gt;
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tagRadio_${i}" id="labelRadio_${i}_190" value="salaryMax">
                                                    <label class="form-check-label" for="labelRadio_${i}_190">
                                                    &lt;salaryMax&gt;
                                                    </label>
                                                </div>                                              
                                            </div>
                                            <div class="col-md">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tagRadio_${i}" id="labelRadio_${i}_20" value="estimatedSalary">
                                                    <label class="form-check-label" for="labelRadio_${i}_20">
                                                    &lt;estimatedSalary&gt;
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tagRadio_${i}" id="labelRadio_${i}_21" value="validThrough">
                                                    <label class="form-check-label" for="labelRadio_${i}_21">
                                                    &lt;validThrough&gt;
                                                    </label>
                                                </div>                                                
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tagRadio_${i}" id="labelRadio_${i}_22" value="hiringOrganization">
                                                    <label class="form-check-label" for="labelRadio_${i}_22">
                                                    &lt;hiringOrganization&gt;
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tagRadio_${i}" id="labelRadio_${i}_23" value="occupationalCategory">
                                                    <label class="form-check-label" for="labelRadio_${i}_23">
                                                    &lt;occupationalCategory&gt;
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tagRadio_${i}" id="labelRadio_${i}_24" value="logoUrl">
                                                    <label class="form-check-label" for="labelRadio_${i}_24">
                                                    &lt;logoUrl&gt;
                                                    </label>
                                                </div>                                                
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tagRadio_${i}" id="labelRadio_${i}_25" value="discard">
                                                    <label class="form-check-label" for="labelRadio_${i}_25">
                                                    Discard
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <div>
                            </td>
                        </tr>
                    `;
                    }
                    
                    $('#parsing').append(mainString);
                    $('#tagNumber').text(`${baseTag.length} results to map:`)
                }
            }
        });
    }
});

/*
This is user page
*/

// Select all function configure

$(document).ready(function () {
    $('#checkAllUser').click(function () {
        $('.userIdSelect').prop('checked', isChecked('checkAllUser'));
    });
});

function isChecked(checkboxId) {
    var id = '#' + checkboxId;
    return $(id).is(":checked");
}

function resetSelectAll() {
    $("#checkAllUser").removeAttr("checked");
    if ($(".userIdSelect").length == $(".userIdSelect:checked").length) {
        $("#checkAllUser").attr("checked", "checked");
    } else {
        $("#checkAllUser").prop( "checked", false );
    }
}

//User page table configuration

$(document).ready(function() {
    $('#user_all').DataTable({
         columnDefs: [
           { 
                orderable: false, targets: 0
           },
           { 
                orderable: false, targets: -1
           }
        ],
         "sortable": false,
         "pageLength": 10,
         "columns": [
            null,
            { "width": "5%" },
            { "width": "25%" },
            { "width": "30%" },
            null,
            null,
          ],
          "bLengthChange": false,
    });
} );

$(document).on("click", "#removeUserHref", function() {
    let userId = $(this).attr("data-id");
    let userIdArray = '';
    if(userId) {
        $("#removeUserId").val(userId);
        $('#removeUser').modal('show');
    }
    else {
        $('input[name="userIdSelect"]:checked').each(function() {
           userIdArray += `${this.value},`;
           $("#removeUserId").val(userIdArray);
        });
        if(userIdArray == "") {
            alert("Please check at least one user");
        }
        else {
            $('#removeUser').modal('show');
        }
    }
});

$(document).on("click", ".removeUser", function() {
    let userId = $("#removeUserId").val();
    $.ajax({
        url: "parsexml.php",
        type: "post",
        dataType: "json",
        data: {"removeUser": "removeOne", "userId": userId},
        success: function(result) {
            if(result) {
                window.location.href = 'user.php';
            }
        }
    })
});

$(document).on("click", ".createUser", function() {
    let userName = $("#userName").val();
    let userEmail = $("#userEmail").val();
    let userPwd = $("#userPwd").val();
    let userPwdCon = $("#userPwdCon").val();
    let userRole = $("#userRole").val();
    if(userName == '' || userEmail == '' || userPwd == '' ||userPwdCon == '') {
        alert("Please check required field");
    }
    else {
           if(userPwd != userPwdCon) {
            alert("Password should be same");
        }

        else {
            $.ajax({
                url: "parsexml.php",
                type: "post",
                dataType: "json",
                data: {"createUser": "createUser", "userName": userName, "userEmail": userEmail, "userPwd": userPwd, "userRole": userRole, "userId": $("#userId").val()},
                success: function(result) {
                    console.log(result);
                    if(result.data == "true") {
                        window.location.href = 'user.php';
                    }
                    else if(result.data == "duplicate") {
                        alert('Name or URL is already stored');
                    }
                    else {
                        alert('Something went wrong while saving feed details');
                    }
                }
            })
        } 
    }

});

$(document).on("click", 'a[data-target="#createUser"]', function() {
    $("h5#createUserLabel").html("Create User");
    $("#userId").val('');
    $("#userName").val('');
    $("#userEmail").val('');
    $("#userPwd").val('');
    $("#userPwdCon").val('');
    $("#userRole option").each(function(){
        $(this).removeAttr('selected');
    });
});

$(document).on("click", "a#editUser", function() {
    $("h5#createUserLabel").html("Edit User");
    var user = $(this).data('user');
    $("#userId").val(user.id);
    $("#userName").val(user.username);
    $("#userEmail").val(user.email);
    $("#userPwd").val(user.password);
    $("#userPwdCon").val(user.password);
    $("#userRole option").each(function(){
        $(this).removeAttr('selected');
        if ($(this).val() == user.role)
            $(this).attr('selected', 'selected');
    });

});

$(document).on("click", ".ai-switch", function() {
    var table = $('#feedinfo').DataTable();
    let feedinfo = $(this).closest('tr').find('input').val();
    let activeTdId = "td#AI-switch-"+feedinfo ; 
    let invalTdId = "#AI-switch-"+feedinfo ;
    let active = $(activeTdId).attr('data-sort');
    if(active == 1){
        $(activeTdId).attr('data-sort', "0");
    }
    else if(active == 0){
        $(activeTdId).attr('data-sort', "1");
    }
    table.cells(invalTdId).invalidate();
    $.ajax({
        url: "parsexml.php",
        type: "post",
        dataType: "json",
        data: {"activeAIGenerate": "activeAIGenerate", "feedinfo": feedinfo},
        success: function(result) {
            console.log(result);
        }
    })
})