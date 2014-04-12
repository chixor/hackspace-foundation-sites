<? 
$page = 'edit';
$title = "Edit your profile";
$desc = '';
require('../header.php');

if (!isset($user)) {
    fURL::redirect('/login.php?forward=/members/profile_edit.php');
}
?>
<h2>Edit Your Member Profile</h2>
<?php
$user_profile = $user->createUsersProfile();
$my_learning = $user->buildLearnings();
$my_aliases = $user->buildUsersAliases();
$my_interests = $user->buildInterests();

if (isset($_POST['disable'])) {
	$user->setDisabledProfile(1);
	$user->store();

    fURL::redirect('profile.php');
    exit;
}
if (isset($_POST['enable'])) {
	$user->setDisabledProfile(0);
	$user->store();	
	
    fURL::redirect('profile.php');
    exit;
}
if (isset($_POST['submit'])) {
    try {
        fRequest::validateCSRFToken($_POST['token']);

		// user profile
		($_POST['allow_email'] && filter_var($_POST['allow_email'], FILTER_SANITIZE_STRING) == 'on') ? 
			$user_profile->setAllowEmail(1):
			$user_profile->setAllowEmail(0);
			
		if($_POST['website'] == 'http://')
			$_POST['website'] = '';

		$user_profile->setWebsite(filter_var($_POST['website'], FILTER_SANITIZE_STRING));
		$user_profile->setDescription(filter_var($_POST['description'], FILTER_SANITIZE_STRING));

		if($_POST['photo-upload']) {
			$filename = $user->getId().'_'.filter_var(str_replace(' ','_',$user->getFullName()), FILTER_SANITIZE_URL).'.png';
			$path = $_SERVER['DOCUMENT_ROOT'] . '/../var/photos/';
			if (!file_exists($path)) {
				mkdir($path, 0777, true);
			}
			file_put_contents($path . $filename, base64_decode(substr($_POST['photo-upload'], strpos($_POST['photo-upload'],",")+1)));
			$user_profile->setPhoto($filename);
		}
		$user_profile->store();
		
		// user learnings
		$list = array();
		foreach($_POST['learnings'] as $key=>$val) {
			array_push($list, filter_var($key, FILTER_SANITIZE_NUMBER_INT));
		}
		$user->setLearnings($list);
		
		// user aliases
		$list = array();
		foreach($_POST['aliases'] as $key=>$val) {
			if($val && $val != null && $val != '')
				$list[filter_var($key, FILTER_SANITIZE_STRING)] = filter_var($val, FILTER_SANITIZE_STRING);
		}
		$user->setAliases($list);
				
		// user interests
		$list = array();
		foreach($_POST['interests'] as $key=>$val) {
			array_push($list, filter_var($key, FILTER_SANITIZE_NUMBER_INT));
		}
		$all_interests = $user->getInterests();
		foreach(explode(',',$_POST['other_interests']) as $val) {
			$search = filter_var(trim($val), FILTER_SANITIZE_STRING);
			if($search != '') {
		        $selected = $all_interests->filter(array('getName=' => $search, 'getCategory=' => 'Other'));
				if($selected->count() > 0) {
					$key = $selected->getInterestId();
				} else {
					$key = $user->addInterest($search,'Other');
				}
				array_push($list, $key);
			}
		}
		$user->setInterests($list);

		$user->setHasProfile(1);
		$user->store();

        fURL::redirect('profile.php');
        exit;
    } catch (fValidationException $e) {
        echo "<p>" . $e->printMessage() . "</p>";
    } catch (fSQLException $e) {
        echo "<p>An unexpected error occurred, please try again later</p>";
        trigger_error($e);
    }

}

if (isset($_GET['saved'])) {
  echo "<div class=\"alert alert-success\"><p>Your profile was updated successfully.</p></div>";
}
?>
<p>We'd love to get to know you better. The information you share here will be available to all paid up members but not the general public.</p>
<form method="post" role="form" class="profile">
<input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />

<div class="row">
	<div class="col-md-3">
		<div class="member-avatar">
            <img src="photo.php?name=<?=$user_profile->getPhoto() ?>"/>
            <input type="hidden" name="photo-upload" id="photo-upload" />
        	<button class="btn btn-primary" id="photo-select">Upload new photo</button>
        	<small class="hidden">'Update profile' to save your photo.</small>
	        <input type="file" name="photo-filesystem" id="photo-filesystem" accept="image/*">
        </div>
        <div class="member-training">
	    	<div class="form-group">
				<div class="btn-group">
				  <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">
				    Trained in the art of... <span class="caret"></span>
				  </button>
				  <ul class="dropdown-menu">
		            <? foreach(fRecordSet::build('Learning') as $training) {?>
					<li><img data-lid="<?=$training->getLearningId()?>" data-name="<?=$training->getName()?>" src="/images/trained-<?=strtolower(str_replace(' ','',$training->getName()));?>.png" class="icon" title="<?=$training->getDescription()?>" /> <?=$training->getName()?></li>
		            <? } ?>
				  </ul>
				</div>
			</div>
	    	<div class="form-group training-badges container">
            <? foreach($my_learning as $training) {?>
				<div class="remove-img"><img src="/images/trained-<?=strtolower(str_replace(' ','',$training->getName()));?>.png"/><input type="hidden" name="learnings[<?=$training->getLearningId()?>]" value="<?=$training->getName()?>" /></div>
            <? } ?>
			</div>
			<p><small><a href="https://wiki.london.hackspace.org.uk/view/Training">More information about training</a></small></p>
        </div>
	</div>
	<div class="col-md-9">
		<? if($user->getDisabledProfile() == 0) { ?>
		<small class="profile_edit"><input type="submit" name="disable" value="Disable my profile" class="btn btn-default btn-sm"/></small>
		<? } else { ?>
		<small class="profile_edit"><input type="submit" name="enable" value="Enable my profile" class="btn btn-primary btn-sm"/></small>
		<? } ?>

		<h3>
			<?= htmlspecialchars($user->getFullName()) ?>
			<p><small><?=$user->getMemberNumber()?><? if($user->firstTransaction() != null) {
				echo ', first joined '.$user->firstTransaction(); 
			}?>
			</small></p>
		</h3>
		<div class="checkbox">
			<label>
				<input type="checkbox" <? if($user_profile->getAllowEmail()) { echo 'checked'; } ?> name="allow_email" id="allow_email"> allow members to see my email address (<a href="mailto:<?=$user->getEmail()?>"><?=$user->getEmail()?></a>)
			</label>
		</div>
	    <div class="form-group personal-site">
	        <label for="website">Website</label>
	        <input type="text" id="website" name="website" class="form-control" value="<? if($user_profile->getWebsite()) { echo $user_profile->getWebsite(); } else { echo 'http://'; } ?>" />
	    </div>    


	    <div class="form-group aliases">
	        <label for="aliases">Aliases</label>
			<div class="alias-fields">
            <? 
            $all_aliases = fRecordSet::build('Aliase');
			foreach($my_aliases as $my_alias) { 
			?>
				<div class="input-group alias-field">
					<input type="text" class="form-control" name="aliases[<?=$my_alias->getAliasId();?>]" value="<?=$my_alias->getUsername();?>">
					<div class="input-group-btn">
				        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><?=$my_alias->getAliasId();?> <span class="caret"></span></button>
				        <ul class="dropdown-menu pull-right">
				            <? foreach($all_aliases as $alias) {?>
							<li><?=$alias->getId()?></li>
				            <? } ?>
				        </ul>
				        <button title="remove" type="button" class="btn btn-default alias-remove">x</button>
				    </div><!-- /btn-group -->
				</div><!-- /input-group -->
            <? } ?>
				<div class="input-group alias-field">
					<input type="text" class="form-control" name="aliases[]" value="">
					<div class="input-group-btn">
				        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Find me on <span class="caret"></span></button>
				        <ul class="dropdown-menu pull-right">
				            <? foreach($all_aliases as $alias) {?>
							<li><?=$alias->getId()?></li>
				            <? } ?>
				        </ul>
				        <button title="remove" type="button" class="btn btn-default alias-remove">x</button>
				    </div><!-- /btn-group -->
				</div><!-- /input-group -->
			</div>
			<button class="btn btn-default add-alias">+ Add another alias</button>			
	    </div>
	
	    <div class="form-group">
	        <strong>Projects I'm working on</strong><br/>
	        <small>The most commonly asked question in the hackspace. What are you doing? Keep it short and sweet.</small>
	        <textarea id="description" name="description" class="form-control" rows="2"><?=$user_profile->getDescription()?></textarea>
	    </div>

	    <div class="form-group interests">
	        <strong>Interests</strong><br/>
        	<small>What else brings you here? Select those which are relevant.</small>
	        <div class="row">
	            <? 
	            $interest_category = ''; 
	            $interest_count = 0;
	            foreach($user->getInterests() as $interest) {
	            	$selected = $my_interests->filter(array('getInterestId=' => $interest->getInterestId()))->count();
	            ?>
					<? if($interest_category != $interest->getCategory()) { 
						$interest_category = $interest->getCategory();
					?>
						<? if($interest_count > 0) { ?>
					</div>
				    <? } ?>
		        	<div class="col-md-3">
		        		<h5><?=$interest->getCategory() ?></h5>
				    <? } ?>
						<div class="checkbox restyle">
							<label <? if($selected) { echo 'class="selected"'; } ?>><input type="checkbox" <? if($selected) { echo 'checked="checked"'; } ?> name="interests[<?=$interest->getInterestId() ?>]" id="trained[<?=$interest->getInterestId() ?>]"> <?=$interest->getName() ?></label>
						</div>
					<? $interest_count++; ?>
		        <? } ?>
				</div>
	        </div>
      		<strong>Other interests</strong><br/>
      		<small>Comma separated list</small><br/>
	        <input type="text" id="interests" name="other_interests" class="form-control bootstrap-tagsinput" value="<? foreach($my_interests as $interest) { if($interest->getCategory() == 'Other') { echo $interest->getName().','; } } ?>" data-role="tagsinput" />
	    </div>	
	    <div class="form-group">
	        <input type="submit" name="submit" value="Update profile" class="btn btn-primary"/>
	    </div>
	</div>
</div>

</form>
<? require('../footer.php'); ?>
<script type="text/javascript" src="/javascript/bootstrap-tagsinput.min.js"></script>
<script>
window.onload = function() {

// add training features
$(".member-training .dropdown-menu li").bind('click touchend', function(e){
    e.stopPropagation();
    e.preventDefault();

	$(this).parents('.btn-group').removeClass('open');
	$('.member-training .training-badges').append('<div class="remove-img"><img src="'+$(this).find('img').attr('src')+'"/><input type="hidden" name="learnings['+$(this).find('img').data('lid')+']" value="'+$(this).find('img').data('name')+'" /></div>');
	addTrainingRemoveEvent($('.member-training .remove-img:last-child'));
    return false;
});
function addTrainingRemoveEvent(obj) {
	obj.bind('click touchend', function(e) {
		e.preventDefault();
		$(this).unbind().remove();
	});
}
addTrainingRemoveEvent($('.member-training .remove-img'));

// add aliases features
$(".aliases .dropdown-menu li").bind('click touchend', function(e){
    e.stopPropagation();
    e.preventDefault();

	$(this).parents('.input-group-btn').removeClass('open').find('.dropdown-toggle').html($(this).text()+' <span class="caret"></span>');
	$(this).parents('.alias-field').find('input').attr('name','aliases['+$(this).text()+']');
    return false;
});
$('.add-alias').bind('click touchend', function(e) {
	e.preventDefault();
	$('.alias-field:first-child').clone(true, true).appendTo( ".alias-fields" );
	$('.alias-field:last-child').find('.dropdown-toggle').html('Find me on <span class="caret"></span>');
	$('.alias-field:last-child').find('input').attr('name','aliases[]').val('');
});
$('.alias-remove').bind('click touchend', function(e) {
	e.preventDefault();
	$(this).parents('.alias-field').remove();
});

// all checkboxes add a class to parent
$('input[type="checkbox"]').bind('change',function() {
	$(this).parent().toggleClass('selected');
});

// photo upload feature
$('.member-avatar #photo-filesystem').bind("change", handleFiles);
$('.member-avatar #photo-select').bind("click touchend", function (e) {
    e.stopPropagation();
    e.preventDefault();

    $('.member-avatar #photo-filesystem').click();
    return false;
});

var canvas = document.createElement('canvas');
var ctx = canvas.getContext("2d");
function handleFiles(e) {
    var reader = new FileReader;
    reader.onload = function (event) {
        var img = new Image();
        img.src = reader.result;
        img.onload = function () {
            var maxWidth = 256,
                maxHeight = 256,
                imageWidth = img.width,
                imageHeight = img.height;

            if (imageWidth > imageHeight) {
                if (imageWidth > maxWidth) {
                    imageHeight *= maxWidth / imageWidth;
                    imageWidth = maxWidth;
                }
            } else {
                if (imageHeight > maxHeight) {
                    imageWidth *= maxHeight / imageHeight;
                    imageHeight = maxHeight;
                }
            }
            canvas.width = imageWidth;
            canvas.height = imageHeight;

            ctx.drawImage(this, 0, 0, imageWidth, imageHeight);

            // The resized file ready for upload
            var finalFile = canvas.toDataURL("image/png");
			$('.member-avatar img').attr('src',finalFile);
			$('.member-avatar #photo-upload').val(finalFile);
			$('.member-avatar #photo-select').blur();
			$('.member-avatar small').removeClass('hidden');
        }
    }
    reader.readAsDataURL(e.target.files[0]);
}
}
</script>
</body>
</html>