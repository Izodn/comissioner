<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/includeList.php';	
	$versionNumber = '4.1.1';
	$patchNotes = 
	'
		<table border=1><tr><td>Version</td><td>Patch Notes</td></tr>
			<tr>
				<td>4.1.1</td>
				<td>
					+Fixed a bug that navigated to gallery-setup when trying to view a commission\'s gallery.<br />
					+Fixed the gallery to have a placeholder image when the original cannot be found.<br /> 
						If this happens, the software also sends out a devLog so we can fix that.<br />
					+Made a couple of security adjustments. Added authenticated database backup.<br />
					+Some admin-tool changes for ease-of-use.
				</td>
			</tr>
			<tr>
				<td>4.1.0</td>
				<td>
					+Rewrote public gallery to support commissions with multiple images. <br />
					+Updated schema in the database to better support future patches.
				</td>
			</tr>
			<tr>
				<td>4.0.4</td>
				<td>A few changes in the software / database to prepare for future patch</td>
			</tr>
			<tr>
				<td>4.0.3</td>
				<td>+Fixed a bug where a user couldn\'t save an image as anything other than .php. <br />
						Now allows jpeg, jpg, png, and gif
				</td>
			</tr>
			<tr>
				<td>4.0.2</td>
				<td>
					+Fixed a bug where some text from the database came out weird.
				</td>
			</tr>
			<tr>
				<td>4.0.1</td>
				<td>
					+Added ability to classify account types as "currency"<br />
						This\'ll give room to have commissions not based on currency.<br />
					+Changed the financial report / download to reflect this change.<br />
						The report won\'t show non-currency commissions.<br/>
				</td>
			</tr>
			<tr>
				<td>4.0.0</td>
				<td>
					+Made commission-specific photos now upload into a private album displaying all images between the commissioner and the requester.<br />
					+Changed the way claiming an account will work. Now when a person attempts to claim an account, it will auto-fill in the username on file<br />
					+Modified the gallery script to allow different viewing "angles" (The URL will determine how it\'s suppose to show it\'s contents)<br />
					+Somewhere along the line, error report suppression was removed from our live-site... It\'s back now. (Sorry)<br />
					+Changed how photos are uploaded: They\'re no-longer deleting same-name files before uploading. It now will add a random number into the - <br />
						name until it can\'t get a match, then uploads
					+clientprofile.php somehow was being handled as if it needs to have a logged-in user, it doesn\'t. <br />
						That\'s fixed now. The problem behind why I fixed<br />
						this is because clientprofile.php is the claim page for un-claimed accounts belonging to commissions. Since this wasn\'t accessible, no accounts <br />
						could be claimed<br />
					+Removed image-descriptions from clientprofile.php until I can figure out when / how to integrate this back in.<br />
					+Added in email validation when creating a user account or commission. This is a required field.<br />
				</td>
			</tr>
		<tr>
			<td>3.9.3</td>
			<td>Made it so galleries aren\'t login-required anymore.<br>
				The gallery list still is loign-required, this change is so commissioners can link their galleries.
			</td>
		</tr>
		<tr>
		<td>3.9.2</td><td>Fixed a bug where quotations would appear in the wrong spot of downloaded financial report.</td>
		</tr>
		<tr>
		<td>3.9.1</td><td>Added a date-picker for the financial report.</td>
		</tr>
		<tr>
		<td>3.9.0</td><td>Added reports for commissioners.
		<br />Commissioners can now run financial and standard commission-list reports.</td>
		</tr>
		<tr>
		<td>3.8.0:</td><td>Finished and made public "Gallery".
		<br>A gallery is where a commissioner can make public, their commission images.
		<br>Users will need to be logged in to see these galleries.
		<br>Users can easily manage their galleries with a tool in the "Settings" section.
		<br>Added "Public Galleries" where any user can see a list of public galleries.
		<br>Improved the security checks involved in shared pages (between commissioners and users).</td>
		</tr>
		<tr>
		<td>3.7.4:</td><td>Nearly finished with public galleries</td>
		</tr>
		<tr>
		<td>3.7.3:</td><td>Added "Results per page" option for tables.</td>
		</tr>
		<tr>
		<td>3.7.2:</td><td>Added in our privacy policy (We don\'t share any information unless legally obligated)</td>
		</tr>
		<tr>
		<td>3.7.1:</td><td>Started framework for a better table-generation.
		<br>Added pages to all tables (Previous, next). 
		<br>The hardcoded limit is 10 results, but this will soon be changeable.
		<br>Added some changes to the still unfinished gallery.</td>
		</tr>
		<tr>
		<td>3.7.0:</td><td>Started work on a better page-generation method that would allow compatibility with really out-dated browsers.
		<br>Added the errorLog-view superuser tool.</td>
		</tr>
		<tr>
		<td>3.6.2:</td><td>Added ability for users to change their password via settings.
		<br>Revised the error-log reporting system a bit.</td>
		</tr>
		<tr>
		<td>3.6.1:</td><td>Added error logs for quicker bug-fixing. <br>Found another very damaging bug with login. This was fixed with another rewrite.</td>
		</tr>
		<tr>
		<td>3.6.0:</td><td>Made large improvements in the system\'s framework to handle object-oriented programming.
		<br>Added payment-method settings for users. This will allow users to have a default method of payment set when entering a commission.</td>
		</tr>
		<tr>
		<td>3.5.1:</td><td>A rewrite of the database credential handling caused a fairly large bug that allowed clients to login as admins.
		<br>This was fixed by a more secure method of cred handling.
		<br>I\'ve also included a feature that allowed site-wide lockdown (Not just user/client disable).</td>
		</tr>
		<tr>
		<td>3.5.0:</td><td>Finished global-variable saving. All cookies are now session-objects.
		<br>This took a huge rewrite and can be broken. I did my best testing, and found none.</td>
		</tr>
		<tr>
		<td>3.4.1:</td><td>Added unique IP counting for statistics.
		<br> Optimized the way the system logs out a user.</td>
		</tr>
		<tr>
		<td>3.4.0:</td><td>Added security enhancements, dynamic "back" button functions. 
		<br>Adjusted the Gallery (Still hidden).
		<br>Starting to change framework to accept self-generating Order Numbers (non-user-input)
		<br>Removed Upload Photo from menu as users will access this through commission pages.
		<br> Changes to commission tables. Now only shows relevant data.
		<br> Added User Profiles: Clients can now see a commissioner\'s statistics
		<br> Added a custom global-variable framework.</td>
		</tr>
		<tr>
		<td>3.3.3:</td><td>Added a beta (hidden) version of Gallery. Also added global variables through session management.
		<br>These changes will make it much easier to update code in the future.</td>
		</tr>
		<tr>
		<td>3.3.2:</td><td>Changed clientprofile.php to not log out an admin user, rather just link to the correct view for them.</td>
		</tr>
		<tr>
		<td>3.3.1:</td><td>Changed the way the software connects to databases. 
		<br>Now the software will dynamically adjust the connection depending on the url.</td>
		</tr>
		<tr>
		<td>3.3.0:</td><td>Security has been enhanced, and a full re-test was done. The software is now release ready.</td>
		</tr>
		<tr>
		<td>3.2.7:</td><td> Optimized data-input and equipped every query to be bind-variable based.
		<br>This is the second-to-last implementation of security fixes.</td>
		</tr>
		<tr>
		<td>3.2.6:</td><td>Changed the query for Search. I plan to change all queries to be very adaptive, and preset.
		<br>This change is one of many optimizations</td>
		</tr>
		<tr>
		<td>3.2.5:</td><td>Added public client registration. The previous process is still existing. 
		<br>Need to do major testing on this feature.
		<br>All users can now auto-fill for any client
		<br>All clients can now request from any user</td>
		</tr>
		<tr>
		<td>3.2.4:</td><td>Fixed the "Reconfirm form submission" issue.</td>
		</tr>
		<tr>
		<td>3.2.3:</td><td>Updated the Commission Progress page. Users now need not check a checkbox to update progress/payment of multiple commissions.
		<br>The checkbox is now used for selected archiving.</td>
		</tr>
		<tr>
		<td>3.2.2:</td><td>Changed how clients will see the home page. There\'s no more drop-box of commission titles. This change makes it easier to view the information from a glance.</td>
		</tr>
		<tr>
		<td>3.2.1:</td><td>Added IP-Ban functionality. Also Added the ability to count unique visitors.</td>
		</tr>
		<tr>
		<td>3.2.0:</td><td>Added client registration based on a previous commission\'s commission page. Made a massive modification to DB to support this change/implimentation. 
		<br>I\'ve also added the ability for clients to request commissions. 
		<br>Users can now view requests and either deny or accept these. Users that accept the request, a price is set by the user that the the client has to accept. 
		<br>After the client accepts this commission, the user can input this info. Need now only fill out Order Number and account type. 
		<br>I\'ve also added an auto-fill feature for past clients. 
		<br>The comments section of a commission page is now implemented. 
		<br>And last but not least, a major security fix. Anything that queries the database now goes through a text filter that warns any superuser of suspicious activity.</td>
		</tr>
		<tr>
		<td>3.1.0:</td><td>Added a security fix for superusers</td>
		</tr>
		<tr>
		<td>3.0.0:</td><td>I\'ve updated the way the software looks. It\'s now much easier on the eyes, due to proper aligning.</td>
		</tr>
		<tr>
		<td>2.2.6:</td><td>Changed functionality of ClientProfileView. If the profile is archived, it acts like it doesn\'t exist to the client. It now disables the ClientView link for the User until unarchived again</td>
		</tr>
		<tr>
		<td>2.2.5:</td><td>Added a check of new vs old when disabling/enabling an account, and proper errors if not enabled/disabled correctly.</td>
		</tr>
		<tr>
		<td>2.2.4:</td><td>Added a table to keep tabs on the un-encrypted username with a unique, matching User ID field. Also added Record counts to keep tabs on how many records a user has.</td>
		</tr>
		<tr>
		<td>2.2.3:</td><td>Fixed a bug where non-auth users could reach auth-user-only pages.</td>
		</tr>
		<tr>
		<td>2.2.2:</td><td> Optimized and secured the Search function. Now this only relies on search.php, not 3 other files.</td>
		</tr>
		<tr>
		<td>2.2.1:</td><td>Fixed a bug where disabling a user\'s account while they\'re logged in didn\'t log them out.</td>
		</tr>
		<tr>
		<td>2.2.0:</td><td> Optimized the software as a whole. Made it much easier to fix things. Added the ability to deactivate/enable specific/all users accept for current user (superuser)</td>
		</tr>
		<tr>
		<td>2.1.3:</td><td>Added better linking of commissions and their profiles.</td>
		</tr>
		<tr>
		<td>2.1.2:</td><td>Made the client-view a little more user-friendly and secure.</td>
		</tr>
		<tr>
		<td>2.1.0:</td><td>Added user-specific clients. You cannot see any client that is not associated with the current logged-in user.</td>
		</tr>
		<tr>
		<td>2.0.5:</td><td> Added multiple-user possibility.</td>
		</tr>
		<tr>
		<td>2.0.4:</td><td>Added encrypted public views (So a client cannot guess/view someone else\'s orderNumber.</td>
		</tr>
		<tr>
		<td>2.0.0:</td><td>Added public profiles for clients with information about THEIR profile.</td>
		</tr>
		<tr>
		<td>1.4.0:</td><td>Added a link on client profiles to navigate to the photo-upload page with needed info already filled in.</td>
		</tr>
		<tr>
		<td>1.3.0:</td><td>Added client profiles (A location with all client information, neatly organized). Also added the ability to upload photos to client profiles.</td>
		</tr>
		<tr>
		<td>1.2.0:</td><td>Sorting improved and now works on Trash, Search, and Progress. </td>
		</tr>
		<tr>
		<td>1.1.3:</td><td>Added sorting, and changed the way tables display headers. Used arrays for better data-fetching and comparing. </td>
		</tr>
		<tr>
		<td>1.1.2:</td><td>Changed from POST to GET when searching. Now changes to a commission while in search will go back to that search page instead of the default view.</td>
		</tr>
		<tr>
		<td>1.1.1:</td><td> Optimized display of table and data</td>
		</tr>
		<tr>
		<td>1.1.0:</td><td>added display of table and data</td>
		</tr>
		<tr>
		<td>1.0.0:</td><td>Started project</td>
		</tr>
		</table>
	';
	echo '<center>';
	if(globalOut('user_key4') && globalOut('user_key5') && globalOut('user_key6') && globalOut('user_key7') && globalOut('user_key3'))
	{
		echoLinks();
	}
	elseif(globalOut('user_key4') && globalOut('user_key5') && globalOut('user_key6') && globalOut('user_key2'))
	{
		echoClientLinks();
	}
	else
	{
		echoLinks();
	}
	echoAdminLinks();
	echo '<br>';
	echo "PHP Version: ".phpversion()."\n<br>\n";
	echo 'Release Version: '.$versionNumber.'<br>'.$patchNotes.'';
	echo '</center>';
?>