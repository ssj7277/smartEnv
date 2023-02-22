<?php

//config
require_once __DIR__ . '/inc/config.php';

//변수 정리
$arrRtn = array(
	'code' => 500,
	'msg' => ''
);

try {

	//검색어
	$sensorSerial = isset($_POST['sensorSerial']) ? $_POST['sensorSerial'] : '';
	$enterDate = isset($_POST['enterDate']) ? $_POST['enterDate'] : '';

	//변수 초기화
	$where = '';
	$max = '';
	$min = '';
	$avg = '';
	$calcQuery = '';
	$calc_result = '';
	$date = '';
	$house = '';



	//날짜 지정
	if ($enterDate) {
		$date = "enterdate LIKE '{$enterDate}%' ";
	}

	//농가번호 지정
	$sensorSerial = $_mysqli->real_escape_string($sensorSerial);
	if ($sensorSerial) {
		$house = "AND sensorSerial = '{$sensorSerial}' ";
	}

	$where .= $date . $house;

	//echo $where;

	if ($sensorSerial) {
		$calcQuery = "
			SELECT MAX(val) AS max, MIN(val) AS min, AVG(val) AS avg
			FROM envdata
			WHERE {$where}
			";
		$query = "SELECT * FROM envdata WHERE {$where}";
	} else {
		//DB 조회
		$query = "SELECT * FROM envdata";
	}


	if ($calcQuery) {
		$calc_result = $_mysqli->query($calcQuery);
	}

	$db_result = $_mysqli->query($query);

	//검색 발생시
	if ($calcQuery) {
		if (!$calc_result) {
			$code = 502;
			$msg = "조회 중 오류가 발생했습니다.(code {$code})\n관리자에게 문의해 주세요.";
			throw new mysqli_sql_exception($msg, $code);
		}
	}

	//DB 조회(검색x)
	if (!$db_result) {
		$code = 502;
		$msg = "조회 중 오류가 발생했습니다.(code {$code})\n관리자에게 문의해 주세요.";
		throw new mysqli_sql_exception($msg, $code);
	}

	//DB 데이터 행 조회
	$dbDataRows = $db_result->num_rows;


	//echo($dbDataRows);
	//exit;


} catch (mysqli_sql_exception $e) {
	$arrRtn['code'] = $e->getCode();
	$arrRtn['msg'] = $e->getMessage();
	echo json_encode($arrRtn);

} catch (Exception $e) {
	$arrRtn['code'] = $e->getCode();
	$arrRtn['msg'] = $e->getMessage();
	echo json_encode($arrRtn);
} finally {
}
?>

<!DOCUTYPE html>
	<html lang="ko">

	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
		<link href="" rel="stylesheet">
		<style>
			body {
				margin-left: 10px;
			}

			table,
			th,
			td {
				border: 1px solid;
			}

			th,
			td {
				text-align: center;
				padding: 10px;
			}
		</style>
		<title>.</title>

	<body>
		<h1>envData</h1><br />
		<!-- 리스트 (전체)조회 -->
		<div>
			<form id="envForm" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
				<fieldset class="select_filedset">
					<div class="">
						<button style="margin-left: 1px;" type="button" onclick="selectEnv()"
							class="btn btn-outline-dark" title="검색버튼" label="검색버튼">전체 조회</button>
					</div>
				</fieldset>
			</form>
		</div>
		<!-- .리스트 조회 -->
		<!-- 검색 -->
		<div>
			<form id="envSearchForm" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
				<fieldset class="select_filedset">
					<div class="">
						<input type="date" name="enterDate" id="enterDate" /><br />
						<label for=""><strong>하우스:</strong></label>
						<select name="sensorSerial" id="sensorSerial">
							<option value="">선택</option>
							<option value="38">이광재님</option>
							<option value="39">김창열님</option>
							<option value="999">테스트</option>
						</select>
						<button style="margin-left: 1px;" type="button" onclick="searchEnv()"
							class="btn btn-outline-primary" title="검색버튼" label="검색버튼">검색</button>
					</div>
				</fieldset>
			</form>
		</div>
		<!-- .검색 -->
		<div>
			<?php if ($calc_result && $dbDataRows) {
				$calcData = $calc_result->fetch_array();
				$min = $calcData['min'];
				$max = $calcData['max'];
				$avg = $calcData['avg'];
				$avg = sprintf('%0.2f', $avg);
			}
			if ($min || $max || $avg) {
				?>
				<table class="" id="">
					<thead>
						<tr>
							<th>농가 번호</th>
							<th>최저 온도</th>
							<th>최고 온도</th>
							<th>평균 온도</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								<?php echo $sensorSerial; ?>
							</td>
							<td>
								<?php echo $min; ?>
							</td>
							<td>
								<?php echo $max; ?>
							</td>
							<td>
								<?php echo $avg; ?>
							</td>
						</tr>
					</tbody>
				</table><br />
			<?php }
			; ?>
		</div>
		<div class="" style="" ;>
			<table class="" id="">
				<colgroup>
				</colgroup>
				<thead>
					<?php
					if ($dbDataRows > 0) { ?>
						<tr>
							<th>No</th>
							<th>데이터 번호</th>
							<th>농가번호</th>
							<th>값</th>
							<th>날짜</th>
						</tr>
					<?php } ?>
				</thead>
				<tbody>
					<?php
					//댓글 리스트
					if ($dbDataRows > 0) {
						$no = 1;
						while ($envData = $db_result->fetch_array()) {
							//변수 정리
							$envSerial = $envData['serial'];
							$envSensor = $envData['sensorSerial'];
							$envValue = $envData['val'];
							$envDate = $envData['enterdate'];
							?>
							<tr>
								<td>
									<?php echo $no; ?>
								</td>
								<td>
									<?php echo $envSerial; ?>
								</td>
								<td>
									<?php echo $envSensor; ?>
								</td>
								<td>
									<?php echo $envValue; ?>
								</td>
								<td>
									<?php echo $envDate; ?>
								</td>
							</tr>
							<?php $no++;
						}
					} else {
						?>
						<tr>
							<td colspan="4"><strong>해당 하우스의 데이터가 없습니다.</strong></td>
						</tr>
						<?php
					} ?>
				</tbody>
			</table>
		</div>

		<footer style="margin: 5em;">
		</footer>
	</body>

	</html>

	<script>
		function selectEnv() {
			envForm = document.getElementById('envForm');
			envForm.submit();
		}

		function searchEnv() {
			var enterDate = document.getElementById('enterDate').value;
			var house = document.getElementById('sensorSerial').value;
			if (enterDate == '') {
				alert('날짜를 선택하세요!!');
				return false;
			}
			if (house == '') {
				alert('하우스를 선택하세요!!');
				return false;
			}

			envSearchForm = document.getElementById('envSearchForm');
			envSearchForm.submit();
		}

		//postData
		let selectHouse = document.getElementById('sensorSerial');
		let selectDate = document.getElementById('enterDate');

		let selectHouseVal = '<?php echo $sensorSerial; ?>';
		let selectDateVal = '<?php echo $enterDate; ?>';

		selectHouse.value = selectHouseVal;
		selectDate.value = selectDateVal;
	</script>