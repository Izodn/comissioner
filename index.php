<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/application.php'; //ALWAYS INCLUDE THIS
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/user.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/links.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/table.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/dbh.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/function/requireLogin.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/function/money.php';
	requireLogin();
	$userId = $_SESSION['userObj']->getUserId();
	if($_SESSION['userObj']->getUserType() === "superuser" || $_SESSION['userObj']->getUserType() === "commissioner") { //Is commissioner / superuser
		global $dbh;
		if(!empty($_GET['autoFill'])) {
			$query = <<<SQL
SELECT
	CFIRSTNAME,
	CLASTNAME,
	CEMAIL
FROM
	COM_USER
WHERE
	CEMAIL = ?
LIMIT
	0,1
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $_GET['autoFill']);
			$runQuery->execute();
			$result = $runQuery->fetch(PDO::FETCH_ASSOC);
			$autoFill = array(
				'firstName' => $result['CFIRSTNAME'],
				'lastName' => $result['CLASTNAME'],
				'email' => $result['CEMAIL']
			);
		}
		$query = <<<SQL
SELECT
	IACCOUNTID
FROM
	COM_ACCOUNT
WHERE
	IUSERID = ?
LIMIT
	0,1
SQL;
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, $userId);
		$runQuery->execute();
		$result = $runQuery->fetch(PDO::FETCH_ASSOC);
		if( $result['IACCOUNTID'] == null ) { //If no payment options go to settings page
			header("Location: settings.php?paymentOptions=Payment+Options");
		}
		if( isset($_POST['submit']) && !empty($_POST['firstName']) && !empty($_POST['lastName']) && !empty($_POST['email']) && !empty($_POST['title']) && !empty($_POST['paymentOption']) ) {
			$cost = 0;
			$description = !empty($_POST['description']) ? $_POST['description'] : "";
			if(!empty($_POST['cost'])) {
				//Expects American English numbers like "$12.50"
				$cost = strToMoney($_POST['cost']);
			}
			/*
			* 2013/11/25 - Brandon
			* We need to create a user for the client that can be "claimed"
			* For this we don't use a pass. Set auto-login to false (4th param).
			* The doCreate function will return false, and set $obj->errMsg. We don't need to handle this.
			* We don't handle the false on create because we only want to create one if one doesn't already exist.
			*/
			//START CLIENT USER CREATE
			$client = new user($_POST['email']); // Create client object
			if(!$client->doCreate($_POST['firstName'], $_POST['lastName'], /*UserType*/null, /*AutoLogin*/false, /*FromCommissionInput*/true))
				$errMsg = $client->errMsg;
			else {
				//END CLIENT USER CREATE
				$query = <<<SQL
INSERT INTO
	COM_COMMISSION(CTITLE, CDESCRIPTION, ICLIENTID, ICOMMISSIONERID, ICOST, IACCOUNTID, IPAYMENTSTATUSID, IPROGRESSSTATUSID, IISARCHIVED, DCREATEDDATE)
VALUES(?, ?, (SELECT IUSERID FROM COM_USER WHERE CEMAIL = ?), ?, ?, ?, 1, 1, 0, NOW())
SQL;
				$runQuery = $dbh->prepare($query);
				$paymentId = $_SESSION['userObj']->getPaymentId($_POST['paymentOption']);
				$runQuery->bindParam(1, $_POST['title']);
				$runQuery->bindParam(2, $description);
				$runQuery->bindParam(3, $_POST['email']);
				$runQuery->bindParam(4, $userId);
				$runQuery->bindParam(5, $cost); //Use already formatted cost var
				$runQuery->bindParam(6, $paymentId);
				if(!$runQuery->execute())
					$errMsg = "Could not submit commission...";
				else
					$successMsg = "Successful!";
			}
		}
		elseif( isset($_POST['submit']) ) {
			$errMsg = "Please fill out all required fields";
		}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Commissioner - Home</title>
		<script>
			formatMoney = function(ele) {
				allowed = ["0","1","2","3","4","5","6","7","8","9"];
				retVal = ''; 
				val = ele.value;
				valLen = val.length;
				for(a=0;a<valLen;a++) {
					if( allowed.indexOf(val[a]) !== -1 ) { //Make sure character is allowed
						if( typeof retVal[0] !== 'undefined' || val[a] !== '0' ) { //Avoid prefixing "0"
							retVal += val[a];
						}
					}
				}
				retValLen = retVal.length;
				if( retValLen >= 2 )
					lastTwo = retVal[retValLen-2]+retVal[retValLen-1];
				else if( retValLen === 1 )
					lastTwo = "0"+retVal[retValLen-1];
				else
					lastTwo = "00";
				buffer="";
				for(a=0;a<retValLen;a++) {
					if(a < retValLen-2)
						buffer += retVal[a];
				}
				if( buffer === "" )
					buffer = "0"
				retVal = buffer+"."+lastTwo;
				if( retVal.length > 0 )
					retVal = '$'+retVal;
				ele.value = retVal;
			}
		</script>
	</head>
	<body>
		<center>
			<?php
				$links = new links($_SESSION['userObj']);
				echo $links->getLinks();
			?>
			<form action="<?php echo htmlentities($_SERVER['REQUEST_URI']); ?>" method="get">
				Autofill for: 
				<select name="autoFill">
					<option>--Choose a client--</option>
					<?php
						$query = <<<SQL
SELECT DISTINCT
	cu.CEMAIL,
	concat(cu.cLastName,', ', cu.cFirstName) as clientName
FROM
	COM_USER cu
INNER JOIN
	COM_COMMISSION cc ON cc.ICLIENTID = cu.IUSERID
WHERE
	cc.ICOMMISSIONERID = ?
ORDER BY
	clientName ASC
SQL;
						$runQuery = $dbh->prepare($query);
						$runQuery->bindParam(1, $userId);
						$runQuery->execute();
						while($row = $runQuery->fetch(PDO::FETCH_ASSOC)) {
							//I don't think we need to hide these emails as they were given to the commissioner initially.
							//We're just displaying name instead for read-ability.
							$clientName = $row['clientName'];
							$tmpClientName = explode(', ', $row['clientName']);
							if( count($tmpClientName) == 2) { //Make sure we have the right number of indexes
								//Here's where we'll trim the names to avoid giant select boxes
								if( strLen($tmpClientName[0]) > 22 ) {
									$tmpClientName[0] = trim(subStr($tmpClientName[0], 0, 22).'...');
								}
								if( strLen($tmpClientName[1]) > 22 ) {
									$tmpClientName[1] = trim(subStr($tmpClientName[1], 0, 22).'...');
								}
								$clientName = implode( $tmpClientName, ', ' );
							}
							echo '<option value="'.$row['CEMAIL'].'"'.(
								!empty($_GET['autoFill']) && $_GET['autoFill'] === $row['CEMAIL'] ? 'selected="selected"' : ''
							).'>'.$clientName.'</option>';
						}
					?>
				</select>
				<input type="submit" name="submit" value="Go">
				<?php
					echo (!empty($_GET['autoFill']) ? '<a href="'.htmlentities($_SERVER['PHP_SELF']).'">Clear</a>' : ''); //Add "clear" link if autoFill is set.
				?>
			</form>
			<form action="<?php echo htmlentities($_SERVER['REQUEST_URI']); ?>" method="post">
				<br>
				<table>
					<tr>
					</tr>
					<tr>
						<td>Client's First Name:<font color="red">*</font></td>
						<td><input type="text" name="firstName" size='32' maxlength="225" <?php echo (!empty($autoFill['firstName']) ? 'value = "'.$autoFill['firstName'].'" readonly="readonly"' : ""); ?>></td>
					</tr>
					<tr>
						<td>Client's Last Name:<font color="red">*</font></td>
						<td><input type="text" name="lastName" size='32' maxlength="225" <?php echo (!empty($autoFill['lastName']) ? 'value = "'.$autoFill['lastName'].'" readonly="readonly"' : ""); ?>></td>
					</tr>
					<tr>
						<td>Client's Email:<font color="red">*</font></td>
						<td><input type="text" name="email" size='32' maxlength="225" <?php echo (!empty($autoFill['email']) ? 'value = "'.$autoFill['email'].'" readonly="readonly"' : ""); ?>></td>
					</tr>
					<tr>
						<td>Commission Title:<font color="red">*</font></td>
						<td><input type="text" name="title" size='16' maxlength="32" ></td>
					<tr>
						<td>Price:<font color="red">*</font></td>
						<td><input type="text" name="cost" size='16' maxlength="8" oninput="formatMoney(this);" value="$0.00"></td>
					</tr>
					<tr>
						<td>Payment Option:<font color="red">*</font></td>
						<td>
							<select name="paymentOption">
							<?php
								$paymentOptions = $_SESSION['userObj']->getPaymentOptions();
								foreach($paymentOptions as $row) {
									echo '<option'.($default = $row['IISDEFAULT'] === '1'?' selected="selected"':'').'>'.$row['CNAME'].'</option>';
								}
							?>
							</select>
						</td>
					</tr>
					<tr>
						<td>Purchase Description:</td>
						<td><textarea cols='26'rows="4" name="description"></textarea>
					</tr>
				</table>
				<input type="submit" name="submit" value="Submit">
				<br>
				<font color="red">*</font> = Required.
			</form>
			<?php
				echo $errMsg = isset($errMsg) ? '<font color="#FF0000">'.$errMsg.'</font>' : ''; //Show error message if exists
				echo $successMsg = isset($successMsg) ? '<font color="#FF0000">'.$successMsg.'</font>' : ''; //Show error message if exists
			?>
		</center>
	</body>
</html>
<?php
	}
	elseif( $_SESSION['userObj']->getUserType() === "client" ) { //Is client
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Commissioner - Progress</title>
	</head>
	<body>
		<center>
			<?php
				global $dbh;
				$links = new links($_SESSION['userObj']);
				echo $links->getLinks();
				$headers = array('Commission ID', 'Title', 'Commissioner Name', 'Description', 'Cost', 'Input Time', 'Progress', 'Payment');
				$query = <<<SQL
SELECT
	cc.iCommissionId,
	cc.cTitle as title,
	concat(cu.cFirstName,' ',cu.cLastName) as commissionerName,
	cc.cDescription as description,
	cc.iCost as cost,
	cc.dCreatedDate as inputTime,
	cpr.cName as progressStatus,
	cpa.cName as paymentStatus,
	cc.iCommissionId as commissionId
FROM
	COM_COMMISSION cc
INNER JOIN
	COM_USER cu ON cu.iUserId = cc.iCommissionerId
INNER JOIN
	COM_PROGRESSSTATUS cpr ON cpr.iStatusId = cc.iProgressStatusId
INNER JOIN
	COM_PAYMENTSTATUS cpa ON cpa.iStatusId = cc.iPaymentStatusId
WHERE
	cc.iClientId = ?
SQL;
				$runQuery = $dbh->prepare($query);
				$runQuery->bindParam(1, $userId);
				$runQuery->execute();
				$result = $runQuery->fetchall(PDO::FETCH_NUM);
				$table = new table($headers, $result);
				$table->setAttr('border',1);
				//START modify each title to link to the unique commission page
				$tableData = $table->dataArr;
				foreach($tableData as $keyA=>$valA) {
					$tableData[$keyA]['Title'] = '<a href="commission.php?id='.$tableData[$keyA]['Commission ID'].'">'.$tableData[$keyA]['Title'].'</a>';
				}
				$table->dataArr = $tableData;
				//STOP modify each title to link to the unique commission page
				$table->changeData('all', 'Cost', 'moneyToStr');
				$table->hideColumn('Commission ID', /*HideHeader*/true, /*HideData*/true, /*ExcludeTh*/true, /*ExcludeTd*/true);
				echo $table->getTable();
			?>
		</center>
	</body>
</html>

<?php
	}
	else {
		//Type not handled, this is bad.
	}
?>