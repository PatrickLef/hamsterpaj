//Configuration variables
var kottmarknad_maxboxes = 9;
var kottmarknad_today = new Date();
var kottmarknad_this_year = kottmarknad_today.getFullYear();
var kottmarknad_userinfo = new Object;

var kottmarknad_girl = 'f';
var kottmarknad_boy = 'm';
var kottmarknad_any = 'all';
var kottmarknad_setupgender = kottmarknad_any;
var kottmarknad_setupgenderplural = 'personer';
var kottmarknad_setupagemin = 10;
var kottmarknad_setupagemax = 30;

var kottmarknad_currentquestion = 0;
var kottmarknad_setupquestions = new Array();

var kottmarknad_error = new Object;
kottmarknad_error.login = 'Du måste vara inloggad för att kunna använda den här tjänsten.';
kottmarknad_error.info = 'Du måste ställa in följande för att kunna använda den här tjänsten:';
kottmarknad_error.info += '<ul>';
kottmarknad_error.info += '<li>Kön</li>';
kottmarknad_error.info += '<li>Födelsedatum</li>';
kottmarknad_error.info += '<li>Bild</li>';
kottmarknad_error.info += '</ul>';
kottmarknad_error.info += '<br />';
kottmarknad_error.info += '<a href="/installningar/generalsettings.php">Ändra inställningarna</a>';



function kottmarknad_updatequestion(qid)
{
	if(qid == 'previous')
	{
		kottmarknad_currentquestion--;
	}
	else if(qid == 'next')
	{
		kottmarknad_currentquestion++;
	}
	else {
		kottmarknad_currentquestion = qid;
	}
	
	kottmarknad_setupquestions[0] = '<h4>Avancerade inställningar</h4>';
	kottmarknad_setupquestions[0] += '<form name="advanced">';
	kottmarknad_setupquestions[0] += 'Jag söker';
	kottmarknad_setupquestions[0] += '<label for="adv_gender1"><input id="adv_gender1" type="radio" name="adv_gender" value="f"' + (kottmarknad_setupgender == kottmarknad_girl ? ' checked="checked"' : '') + ' />Tjejer</label>';
	kottmarknad_setupquestions[0] += '<label for="adv_gender2"><input id="adv_gender2" type="radio" name="adv_gender" value="m"' + (kottmarknad_setupgender == kottmarknad_boy ? ' checked="checked"' : '') + ' />Killar</label>';
	kottmarknad_setupquestions[0] += '<label for="adv_gender3"><input id="adv_gender3" type="radio" name="adv_gender" value="all"' + (kottmarknad_setupgender == kottmarknad_any ? ' checked="checked"' : '') + ' />Alla</label><br />';
	kottmarknad_setupquestions[0] += 'Mellan';
	kottmarknad_setupquestions[0] += '<input class="adv_agebox" type="text" name="adv_agemin" value="' + kottmarknad_setupagemin + '" /> och ';
	kottmarknad_setupquestions[0] += '<input class="adv_agebox" type="text" name="adv_agemax" value="' + kottmarknad_setupagemax + '" /><br />';
	kottmarknad_setupquestions[0] += '<input class="button" type="button" value="Ok" onClick="kottmarknad_confirmadvanced();" /><br />';
	kottmarknad_setupquestions[0] += '<br />';
	kottmarknad_setupquestions[0] += '<a onClick="kottmarknad_updatequestion(\'next\');">&laquo; Tillbaka</a>';
	kottmarknad_setupquestions[0] += '</form>';
	
	kottmarknad_setupquestions[1] = 'Hej ' + kottmarknad_userinfo.username + '!<br />';
	kottmarknad_setupquestions[1] += '<p>Livet slutar i en paj om man inte tar vara på det. Och hur tar man bättre vara på livet än delar det med någon annan?</p>';
	kottmarknad_setupquestions[1] += '<p>Det är inte alltid lätt att hitta någon trevlig man kan dela livet med. Därför, gott folk, har vi på Hamsterpaj beslutat oss för att hjälpa dig lite på traven. Du har nämnligen trillat in på Köttmarknaden - en marknad för oss köttklumpar - som förklaras enklast med ordet matchmaking.</p>';
	kottmarknad_setupquestions[1] += '<p>Som gammal romantiker kommer jag, Hamstern, hjälpa dig genom hela guiden.</p>';
	kottmarknad_setupquestions[1] += '<br />';
	kottmarknad_setupquestions[1] += '<a onClick="kottmarknad_updatequestion(\'next\');">Fortsätt &raquo;</a>';
	
	kottmarknad_setupquestions[2] = '<p>Vilket kön söker du?</p>';
	kottmarknad_setupquestions[2] += '<a class="genderbtn" onClick="kottmarknad_loadgender(\'' + kottmarknad_girl + '\');"><img src="http://images.hamsterpaj.net/traffa/kvinna.png" /><br />Tjejer</a>';
	kottmarknad_setupquestions[2] += '<a class="genderbtn" onClick="kottmarknad_loadgender(\'' + kottmarknad_boy + '\');"><img src="http://images.hamsterpaj.net/traffa/man.png" /><br />Killar</a>';
	kottmarknad_setupquestions[2] += '<a class="genderbtn" onClick="kottmarknad_loadgender(\'' + kottmarknad_any + '\');"><img src="http://images.hamsterpaj.net/traffa/shemale.png" /><br />Båda</a>';
	
	kottmarknad_setupquestions[3] = '<p>Jag vill inte para ihop dig med någon pedofil eller annat otyg, så därför ställer jag dig frågan:</p>';
	kottmarknad_setupquestions[3] += '<p>Ska jag bara visa de ' + kottmarknad_setupgenderplural + ' som är jämngamla med dig?</p>';
	kottmarknad_setupquestions[3] += '<input class="button" type="button" value="Ja" onClick="kottmarknad_selectyear(\'' + kottmarknad_userinfo.gender + '\')" />';
	kottmarknad_setupquestions[3] += '<input class="button" type="button" value="Nej" onClick="kottmarknad_selectyear(\'\', ' + kottmarknad_setupagemin + ', ' + kottmarknad_setupagemax + ')" />';
	
	kottmarknad_setupquestions[4] = '<p>Då var det färdigt. ;)</p>';
	kottmarknad_setupquestions[4] += '<p>Nu är det bara att välja och vraka bland de ' + kottmarknad_setupgenderplural + ' jag tagit fram här. Om du är riktigt kräsen så kan du gå till ';
	kottmarknad_setupquestions[4] += '<a onClick="kottmarknad_updatequestion(0)">avancerade inställningar</a> och försöka hitta den perfekta för dig, men förvänta dig inte min hjälp då.</p>';
	
	//Disable navigation buttons if there's no questions left
	var numberofquestions = kottmarknad_setupquestions.length - 1;
	if((kottmarknad_currentquestion - 1) < 1)
	{
		$("#nav_back").attr("disabled", "disabled");
		$("#nav_back").removeClass("optionbutton_hover");
	}
	else
	{
		$("#nav_back").removeAttr("disabled");
		$("#nav_back").addClass("optionbutton_hover");
	}
	if((kottmarknad_currentquestion + 1) > numberofquestions)
	{
		$("#nav_forward").attr("disabled", "disabled");
		$("#nav_forward").removeClass("optionbutton_hover");
	}
	else
	{
		$("#nav_forward").removeAttr("disabled");
		$("#nav_forward").addClass("optionbutton_hover");
	}
	
	kottmarknad_changeboxcontent(kottmarknad_setupquestions[kottmarknad_currentquestion]);
}



function kottmarknad_changeboxcontent(inputstr)
{
	//Fade out, change content and then fade in again
	$(".setupquestion").fadeOut("normal", function() {
		$(".setupquestion").html(inputstr);
		$(".setupquestion").fadeIn("normal");
	});
}



function kottmarknad_updateboxes()
{
	//Remove all boxes
	$(".userboxcontainer .userbox").hide("slow", function() {
		$(this).remove();
	});
	
	//Load genders
	kottmarknad_loadgender(kottmarknad_setupgender, "update");
}



function kottmarknad_loadgender(gender, action)
{
	//Store gender in a public variable
	kottmarknad_setupgender = gender;
	kottmarknad_setupgenderplural = kottmarknad_setupgender == kottmarknad_boy ? 'killar' : (kottmarknad_setupgender == kottmarknad_girl ? 'tjejer' : 'personer');
	
	//Indicate too old/young persons
	$(".userboxcontainer .userbox").each(function() {
		var class_string = $(this).attr("class");
		var birth_match = class_string.search("birth");
		var birth_year = parseInt(class_string.substr(birth_match + 5));
		if(birth_year >= (kottmarknad_this_year - kottmarknad_setupagemin) || birth_year <= (kottmarknad_this_year - kottmarknad_setupagemax))
		{
			$(this).addClass("remove");
		}
	});
	
	//Define the opposite gender
	var oppositegender = (gender == kottmarknad_boy ? 'female' : (gender == kottmarknad_girl ? 'male' : 'userbox'));
	
	//Indicate unrelated boxes
	$(".userboxcontainer ." + oppositegender).addClass("remove");
	
	//Count unrelated boxes - set to maximum boxes on update
	var numboxes = (action == 'update' ? kottmarknad_maxboxes : $(".userboxcontainer .remove").length);
	
	if(action != 'update')
	{
		//Indicate the unrelateds' popup information boxes
		$(".userboxcontainer .remove").each(function() {
			$(".userboxcontainer .displayuser#wnd_" + $(this).attr("id")).addClass("remove");
		});
	}
	else
	{
		//Indicate all popup information boxes
		$(".userboxcontainer .displayuser").addClass("remove");
	}
	
	//Remove indicated (unrelated) boxes
	$(".userboxcontainer .remove").hide("slow", function() {
		$(this).remove();
	});
	
	//Define existing boxes
	var existingboxes = '';
	$(".userboxcontainer .userbox:not(.remove)").each(function() {
		existingboxes += ',' + $(this).attr("id");
	});
	
	//Make an ajax request
	var ajax_request = '/ajax_gateways/traffa.new.php?gender=' + kottmarknad_setupgender + '&amount=' + numboxes + '&existingboxes=' + existingboxes + '&agemin=' + kottmarknad_setupagemin + '&agemax=' + kottmarknad_setupagemax;
	$.get(ajax_request, function(data){
	  $(".userboxcontainer").append(data);
	  $(".userboxcontainer .newbox").show("normal");
	  $(".userboxcontainer .newbox").removeClass("newbox");
	});
	
	//Change search description
	if(action != 'update') $("#search_desc").fadeOut("fast", function() {
		$("#search_desc").html('Du söker ' + kottmarknad_setupgenderplural + ' mellan ' + kottmarknad_setupagemin + ' och ' + kottmarknad_setupagemax + '. <a href="javascript:void(0);" onClick="kottmarknad_updatequestion(0);">Ändra &raquo;</a>');
		$("#search_desc").fadeIn("slow");
	});
	
	//Display next question
	if(action != 'update' && action != 'noquestion') kottmarknad_updatequestion('next');
}



function kottmarknad_selectyear(gender, minage, maxage)
{
	if(gender != '')
	{
		//Use recommanded age margins
		if(gender == kottmarknad_boy)
		{
			kottmarknad_setupagemin = kottmarknad_userinfo.age - 2;
			kottmarknad_setupagemax = kottmarknad_userinfo.age + 1;
		}
		else if(gender == kottmarknad_girl)
		{
			kottmarknad_setupagemin = kottmarknad_userinfo.age - 1;
			kottmarknad_setupagemax = kottmarknad_userinfo.age + 2;
		}
	}
	else
	{
		//Use specified age margins
		kottmarknad_setupagemin = minage;
		kottmarknad_setupagemax = maxage;
	}
	kottmarknad_loadgender(kottmarknad_setupgender);
}



function kottmarknad_confirmadvanced()
{
	var regexpattern = /^[0-9]+$/;
	
	//Fetch information from advanced search form
	if($("form[name='advanced'] input[name='adv_gender']:checked").val() && $("form[name='advanced'] input[name='adv_agemin']").val() && $("form[name='advanced'] input[name='adv_agemax']").val() && regexpattern.test($("form[name='advanced'] input[name='adv_agemin']").val()) && regexpattern.test($("form[name='advanced'] input[name='adv_agemax']").val()))
	{
		kottmarknad_setupgender = $("form[name='advanced'] input[name='adv_gender']:checked").val();
		kottmarknad_setupagemin = parseInt($("form[name='advanced'] input[name='adv_agemin']").val());
		kottmarknad_setupagemax = parseInt($("form[name='advanced'] input[name='adv_agemax']").val());
		kottmarknad_loadgender(kottmarknad_setupgender, 'noquestion');
		kottmarknad_updatequestion(1);
	}
	else
	{
		kottmarknad_updatequestion(1);
	}
}



function kottmarknad_displayuser(uid, gender)
{
	var original = $("#" + uid).offset();
	
	//If the userinfo window doesn't exist then create it and display the information. Else, close the window.
	if(!$(".userinfownd").text())
	{
		var content = '<div class="userinfownd ' + (gender == kottmarknad_boy ? 'male' : (gender == kottmarknad_girl ? 'female' : '')) + '"><img style="position: absolute; top: -3px; right: -3px; z-index: 9999; cursor: pointer;" onClick="kottmarknad_displayuser(' + uid + ');" src="http://images.hamsterpaj.net/admin_todo/cancel.png" /><div class="userinfownd_content">' + $("#wnd_" + uid).html() + '</div></div>';
		$(".content").append(content);
		$(".userinfownd").css({"left" : original.left + "px", "top" : original.top + "px", "opacity" : "0.0"});
		$(".userinfownd").animate({"opacity" : "1.0", "left" : "150px", "top" : $(document).scrollTop() + 150 + "px", "width" : "488px", "height" : "288px"}, 350, function() {
			$(".userinfownd .userinfownd_content").fadeIn(300);
			$(".userinfownd #kottmarknad_card_message").focus();
		});
	}
	else
	{
		$(".userinfownd .userinfownd_content").hide();
		$(".userinfownd").animate({"opacity" : "0.0", "left" : original.left + "px", "top" : original.top + "px", "width" : $("#" + uid).width() + "px", "height" : $("#" + uid).height() + "px"}, 500, function() {
			$(".userinfownd").remove();
		});
	}
}



function kottmarknad_getuserinfo()
{
	//Fetch information about the user
	kottmarknad_userinfo.username = $("form[name='userinfo'] input[name='userinfo_username']").val();
	kottmarknad_userinfo.gender = $("form[name='userinfo'] input[name='userinfo_gender']").val();
	kottmarknad_userinfo.birth = parseInt($("form[name='userinfo'] input[name='userinfo_birth']").val());
	kottmarknad_userinfo.avatar = parseInt($("form[name='userinfo'] input[name='userinfo_avatar']").val());
	kottmarknad_userinfo.age = kottmarknad_this_year - kottmarknad_userinfo.birth;
	kottmarknad_userinfo.checklogin = (kottmarknad_userinfo.username != '' ? true : false);
	kottmarknad_userinfo.environment = $("form[name='userinfo'] input[name='userinfo_environment']").val();
}



function kottmarknad_sendmessage()
{
	//Fetch guestbook entry information
	$(".userinfownd form[name='kottmarknad_card'] input[type='button']").attr("disabled", "disabled");
	var recipient = $(".userinfownd form[name='kottmarknad_card'] input[name='recipient']").val();
	var message = $(".userinfownd form[name='kottmarknad_card'] textarea[name='message']").val();
	
	//Send away a new guestbook entry
	$.post('/ajax_gateways/guestbook.json.php', {"action" : "insert", "recipient" : recipient, "message" : message, "private" : "1"}, function() {
		$(".userinfownd form[name='kottmarknad_card']").fadeOut("normal", function() {
			$(".userinfownd form[name='kottmarknad_card']").html('<p>Skickat.</p>');
			$(".userinfownd form[name='kottmarknad_card']").fadeIn("normal");
		});
	});
	
	return true;
}



$(document).ready(function() {
	//Gather info about the user
	kottmarknad_getuserinfo();
	
	//Set onClick properties on all the buttons
	$("#nav_back").click(function() {kottmarknad_updatequestion('previous')});
	$("#nav_adv").click(function() {kottmarknad_updatequestion(0)});
	$("#nav_forward").click(function() {kottmarknad_updatequestion('next')});
	$(".reloadbutton").click(function() {kottmarknad_updateboxes()});
	
	if(kottmarknad_userinfo.checklogin)
	{
		if(kottmarknad_userinfo.gender != 'u' && kottmarknad_userinfo.birth > 0 && (kottmarknad_userinfo.avatar > 0 || kottmarknad_userinfo.environment == 'development'))
		{
			//Load all genders in update action
			kottmarknad_loadgender('all', 'update');
			
			//Display the first question
			kottmarknad_updatequestion(1);
		}
		else
		{
			//Some information is missing
			kottmarknad_changeboxcontent(kottmarknad_error.info);
			$(".content input[type='button']").attr("disabled", "disabled");
			$(".content input[type='button']").removeClass("optionbutton_hover");
		}
	}
	else
	{
		//User isn't logged in
		kottmarknad_changeboxcontent(kottmarknad_error.login);
		$(".content input[type='button']").attr("disabled", "disabled");
		$(".content input[type='button']").removeClass("optionbutton_hover");
	}
});