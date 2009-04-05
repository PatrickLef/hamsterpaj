//Configuration variables
var maxboxes = 9;
var today = new Date();
var this_year = today.getFullYear();
var userinfo = new Object;

var girl = 'f';
var boy = 'm';
var any = 'all';
var setupgender = any;
var setupgenderplural = 'personer';
var setupagemin = 10;
var setupagemax = 30;

var currentquestion = 0;
var setupquestions = new Array();

var error = new Object;
error.login = 'Du måste vara inloggad för att kunna använda den här tjänsten.';
error.info = 'Du måste ställa in följande för att kunna använda den här tjänsten:';
error.info += '<ul>';
error.info += '<li>Kön</li>';
error.info += '<li>Födelsedatum</li>';
error.info += '<li>Bild</li>';
error.info += '</ul>';
error.info += '<br />';
error.info += '<a href="http://igge.t67.se/installningar/generalsettings.php">Ändra inställningarna</a>';


function updatequestion(qid)
{
	if(qid == 'previous')
	{
		currentquestion--;
	}
	else if(qid == 'next')
	{
		currentquestion++;
	}
	else {
		currentquestion = qid;
	}
	
	setupquestions[0] = '<h4>Avancerade inställningar</h4>';
	setupquestions[0] += '<form name="advanced">';
	setupquestions[0] += 'Jag söker';
	setupquestions[0] += '<label for="adv_gender1"><input id="adv_gender1" type="radio" name="adv_gender" value="f"' + (setupgender == girl ? ' checked="checked"' : '') + ' />Tjejer</label>';
	setupquestions[0] += '<label for="adv_gender2"><input id="adv_gender2" type="radio" name="adv_gender" value="m"' + (setupgender == boy ? ' checked="checked"' : '') + ' />Killar</label>';
	setupquestions[0] += '<label for="adv_gender3"><input id="adv_gender3" type="radio" name="adv_gender" value="all"' + (setupgender == any ? ' checked="checked"' : '') + ' />Alla</label><br />';
	setupquestions[0] += 'Mellan';
	setupquestions[0] += '<input class="adv_agebox" type="text" name="adv_agemin" value="' + setupagemin + '" /> och ';
	setupquestions[0] += '<input class="adv_agebox" type="text" name="adv_agemax" value="' + setupagemax + '" /><br />';
	setupquestions[0] += '<input class="button" type="button" value="Ok" onClick="confirmadvanced();" /><br />';
	setupquestions[0] += '<br />';
	setupquestions[0] += '<a onClick="updatequestion(\'next\');">&laquo; Tillbaka</a>';
	setupquestions[0] += '</form>';
	
	setupquestions[1] = 'Hej ' + userinfo.username + '!<br />';
	setupquestions[1] += '<p>Livet slutar i en paj om man inte tar vara på det. Och hur tar man bättre vara på livet än delar det med någon annan?</p>';
	setupquestions[1] += '<p>Det är inte alltid lätt att hitta någon trevlig man kan dela livet med. Därför, gott folk, har vi på Hamsterpaj beslutat oss för att hjälpa dig lite på traven. Du har nämnligen trillat in på Köttmarknaden - en marknad för oss köttklumpar - som förklaras enklast med ordet matchmaking.</p>';
	setupquestions[1] += '<p>Som gammal romantiker kommer jag, Hamstern, hjälpa dig genom hela guiden.</p>';
	setupquestions[1] += '<br />';
	setupquestions[1] += '<a onClick="updatequestion(\'next\');">Fortsätt &raquo;</a>';
	
	setupquestions[2] = '<p>Vilket kön söker du?</p>';
	setupquestions[2] += '<a class="genderbtn" onClick="loadgender(\'' + girl + '\');"><img src="http://images.hamsterpaj.net/traffa/kvinna.png" /><br />Tjejer</a>';
	setupquestions[2] += '<a class="genderbtn" onClick="loadgender(\'' + boy + '\');"><img src="http://images.hamsterpaj.net/traffa/man.png" /><br />Killar</a>';
	setupquestions[2] += '<a class="genderbtn" onClick="loadgender(\'' + any + '\');"><img src="http://images.hamsterpaj.net/traffa/shemale.png" /><br />Båda</a>';
	
	setupquestions[3] = '<p>Jag vill inte para ihop dig med någon pedofil eller annat otyg, så därför ställer jag dig frågan:</p>';
	setupquestions[3] += '<p>Ska jag bara visa de ' + setupgenderplural + ' som är jämngamla med dig?</p>';
	setupquestions[3] += '<input class="button" type="button" value="Ja" onClick="selectyear(\'' + userinfo.gender + '\')" />';
	setupquestions[3] += '<input class="button" type="button" value="Nej" onClick="selectyear(\'\', ' + setupagemin + ', ' + setupagemax + ')" />';
	
	setupquestions[4] = '<p>Då var det färdigt. ;)</p>';
	setupquestions[4] += '<p>Nu är det bara att välja och vraka bland de ' + setupgenderplural + ' jag tagit fram här. Om du är riktigt kräsen så kan du gå till ';
	setupquestions[4] += '<a onClick="updatequestion(0)">avancerade inställningar</a> och försöka hitta den perfekta för dig, men förvänta dig inte min hjälp då.</p>';
	
	//Disable navigation buttons if there's no questions left
	var numberofquestions = setupquestions.length - 1;
	if((currentquestion - 1) < 1)
	{
		$("#nav_back").attr("disabled", "disabled");
		$("#nav_back").removeClass("optionbutton_hover");
	}
	else
	{
		$("#nav_back").removeAttr("disabled");
		$("#nav_back").addClass("optionbutton_hover");
	}
	if((currentquestion + 1) > numberofquestions)
	{
		$("#nav_forward").attr("disabled", "disabled");
		$("#nav_forward").removeClass("optionbutton_hover");
	}
	else
	{
		$("#nav_forward").removeAttr("disabled");
		$("#nav_forward").addClass("optionbutton_hover");
	}
	
	changeboxcontent(setupquestions[currentquestion]);
}



function changeboxcontent(inputstr)
{
	//Fade out, change content and then fade in again
	$(".setupquestion").fadeOut("normal", function() {
		$(".setupquestion").html(inputstr);
		$(".setupquestion").fadeIn("normal");
	});
}



function updateboxes()
{
	//Remove all boxes
	$(".userboxcontainer .userbox").hide("slow", function() {
		$(this).remove();
	});
	
	//Load genders
	loadgender(setupgender, "update");
}



function loadgender(gender, action)
{
	//Store gender in a public variable
	setupgender = gender;
	setupgenderplural = setupgender == boy ? 'killar' : (setupgender == girl ? 'tjejer' : 'personer');
	
	//Indicate too old/young persons
	$(".userboxcontainer .userbox").each(function() {
		var class_string = $(this).attr("class");
		var birth_match = class_string.search("birth");
		var birth_year = parseInt(class_string.substr(birth_match + 5));
		if(birth_year <= (userinfo.birth - setupagemin) || birth_year >= (userinfo.birth + setupagemax))
		{
			$(this).addClass("remove");
		}
	});
	
	//Define the opposite gender
	var oppositegender = (gender == boy ? 'female' : (gender == girl ? 'male' : 'userbox'));
	
	//Indicate unrelated boxes
	$(".userboxcontainer ." + oppositegender).addClass("remove");
	
	//Count unrelated boxes - set to maximum boxes on update
	var numboxes = (action == 'update' ? maxboxes : $(".userboxcontainer .remove").length);
	
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
	$(".userboxcontainer .userbox:not(.remove)").each(function(i) {
		existingboxes += ',' + $(this).attr("id");
	});
	
	//Make an ajax request
	var ajax_request = '/ajax_gateways/traffa.new.php?gender=' + gender + '&amount=' + numboxes + '&existingboxes=' + existingboxes + '&agemin=' + setupagemin + '&agemax=' + setupagemax;
	$.get(ajax_request, function(data){
	  $(".userboxcontainer").append(data);
	  $(".userboxcontainer .newbox").show("normal");
	  $(".userboxcontainer .newbox").removeClass("newbox");
	});
	
	//Change search description
	if(action != 'update') $("#search_desc").fadeOut("fast", function() {
		$("#search_desc").html('Du söker ' + setupgenderplural + ' mellan ' + setupagemin + ' och ' + setupagemax + '. <a href="javascript:void(0);" onClick="updatequestion(0);">Ändra &raquo;</a>');
		$("#search_desc").fadeIn("slow");
	});
	
	//Display next question
	if(action != 'update' && action != 'noquestion') updatequestion('next');
}



function selectyear(gender, minage, maxage)
{
	if(gender != '')
	{
		//Use recommanded age margins
		if(gender == boy)
		{
			setupagemin = userinfo.age - 2;
			setupagemax = userinfo.age + 1;
		}
		else if(gender == girl)
		{
			setupagemin = userinfo.age - 1;
			setupagemax = userinfo.age + 2;
		}
	}
	else
	{
		//Use specified age margins
		setupagemin = minage;
		setupagemax = maxage;
	}
	loadgender(setupgender);
}



function confirmadvanced()
{
	var regexpattern = /^[0-9]+$/;
	if($("form[@name='advanced'] input[@name='adv_gender']:checked").val() && $("form[@name='advanced'] input[@name='adv_agemin']").val() && $("form[@name='advanced'] input[@name='adv_agemax']").val() && regexpattern.test($("form[@name='advanced'] input[@name='adv_agemin']").val()) && regexpattern.test($("form[@name='advanced'] input[@name='adv_agemax']").val()))
	{
		setupgender = $("form[@name='advanced'] input[@name='adv_gender']:checked").val();
		setupagemin = parseInt($("form[@name='advanced'] input[@name='adv_agemin']").val());
		setupagemax = parseInt($("form[@name='advanced'] input[@name='adv_agemax']").val());
		loadgender(setupgender, 'noquestion');
		updatequestion(1);
	}
	else
	{
		updatequestion(1);
	}
}



function displayuser(uid, gender)
{
	var original = $("#" + uid).offset();
	if(!$(".userinfownd").text())
	{
		var content = '<div class="userinfownd ' + (gender == boy ? 'male' : (gender == girl ? 'female' : '')) + '"><img style="position: absolute; top: -3px; right: -3px; z-index: 9999; cursor: pointer;" onClick="displayuser(' + uid + ');" src="http://images.hamsterpaj.net/admin_todo/cancel.png" /><div class="userinfownd_content">' + $("#wnd_" + uid).html() + '</div></div>';
		$(".content").append(content);
		$(".userinfownd").css({"left" : original.left + "px", "top" : original.top + "px", "opacity" : "0.0"});
		$(".userinfownd").animate({"opacity" : "1.0", "left" : "150px", "top" : $(document).scrollTop() + 150 + "px", "width" : "488px", "height" : "288px"}, 350, function() {
			$(".userinfownd .userinfownd_content").fadeIn(300);
			//$(".userinfownd #kottmarknad_card").submit(function() {return false;});
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



function getuserinfo()
{
	userinfo.username = $("form[@name='userinfo'] input[@name='userinfo_username']").val();
	userinfo.gender = $("form[@name='userinfo'] input[@name='userinfo_gender']").val();
	userinfo.birth = parseInt($("form[@name='userinfo'] input[@name='userinfo_birth']").val());
	userinfo.avatar = parseInt($("form[@name='userinfo'] input[@name='userinfo_avatar']").val());
	userinfo.age = this_year - userinfo.birth;
	userinfo.checklogin = (userinfo.username != '' ? true : false);
}



function sendmessage()
{
	$(".userinfownd form[@name='kottmarknad_card'] input[@type='button']").attr("disabled", "disabled");
	var recipient = $(".userinfownd form[@name='kottmarknad_card'] input[@name='recipient']").val();
	var message = $(".userinfownd form[@name='kottmarknad_card'] textarea[@name='message']").val();
	
	$.post('/ajax_gateways/guestbook.json.php', {"action" : "insert", "recipient" : recipient, "message" : message, "private" : "1"}, function() {
		$(".userinfownd form[@name='kottmarknad_card']").fadeOut("normal", function() {
			$(".userinfownd form[@name='kottmarknad_card']").html('<p>Skickat.</p>');
			$(".userinfownd form[@name='kottmarknad_card']").fadeIn("normal");
		});
	});
	
	return true;
}



$(document).ready(function() {
	//Gather info about the user
	getuserinfo();
	
	$("#nav_back").click(function() {updatequestion('previous')});
	$("#nav_adv").click(function() {updatequestion(0)});
	$("#nav_forward").click(function() {updatequestion('next')});
	$(".reloadbutton").click(function() {updateboxes()});
	
	if(userinfo.checklogin)
	{
		if(userinfo.gender != 'u' && userinfo.birth > 0 && userinfo.avatar > 0)
		{
			//Load all genders in update action
			loadgender('all', 'update');
			
			//Display the first question
			updatequestion(1);
		}
		else
		{
			//Some information is missing
			changeboxcontent(error.info);
			$(".content input[@type='button']").attr("disabled", "disabled");
			$(".content input[@type='button']").removeClass("optionbutton_hover");
		}
	}
	else
	{
		//User isn't logged in
		changeboxcontent(error.login);
		$(".content input[@type='button']").attr("disabled", "disabled");
		$(".content input[@type='button']").removeClass("optionbutton_hover");
	}
});