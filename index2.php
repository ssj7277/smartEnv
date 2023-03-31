<?php

//config
require_once __DIR__ . '/inc/config.php';

//변수 정리
$arrRtn = array(
    'code' => 500,
    'msg'  => ''
);

try {

    //paging
    $records_per_page = 1000;
    $current_page     = isset( $_GET['page'] ) ? $_GET['page'] : 1;

    //검색어
    $sensorSerial = isset( $_POST['sensor'] ) ? $_POST['sensor'] : '';
    $month        = isset( $_POST['month'] ) ? $_POST['month'] : '';

    //변수 초기화
    $where       = '';
    $max         = '';
    $min         = '';
    $avg         = '';
    $calcQuery   = '';
    $calc_result = '';
    $date        = '';
    $house       = '';

    $enterdate = '2023-';

    //날짜 지정
    if ($month) {
        $enterdate = $enterdate . $month;
        $date      = "AND enterdate LIKE '{$enterdate}%' ";
    }

    //농가 지정
    switch ($sensorSerial) {
        case 38:
            $house = '이광재 하우스';
            break;
        case 39:
            $house = '김창열 하우스';
            break;
        case 'appleTemp':
            $house = '사과 하우스(온도)';
            $sensorSerial = '4' . "','" . ' 13';
            break;
        case 'appleHumi1':
            $house = '사과하우스1(습도)';
            $sensorSerial = '1' . "','" . ' 2' . "','" . ' 3' . "','" . ' 5' . "','" . ' 6' . "','" . ' 7';
            break;
        case 'appleHumi2':
            $house = '사과하우스2(습도)';
            $sensorSerial = '10' . "','" . ' 11' . "','" . ' 12' . "','" . ' 14' . "','" . ' 15' . "','" . ' 16';
            break;
        case 'grapeHumi13':
            $house = '포도 하우스 13동(습도)';
            $sensorSerial = '17' . "','" . ' 18' . "','" . ' 19' . "','" . ' 20' . "','" . ' 21' . "','" . ' 22';
            break;
        case 'grapeHumi14':
            $house = '포도 하우스 14동(습도)';
            $sensorSerial = '23' . "','" . ' 24' . "','" . ' 25' . "','" . ' 26' . "','" . ' 27' . "','" . ' 28';
            break;
        case 'daecuHumi':
            $house = '대추하우스(습도)';
            $sensorSerial = '29' . "','" . ' 30' . "','" . ' 31';
            break;
        case 'sanchoHumi':
            $house = '산초하우스(습도)';
            $sensorSerial = '32' . "','" . ' 33' . "','" . ' 34';
            break;
        case 'asparagusHumi':
            $house = '아스파라거스(습도)';
            $sensorSerial = '35' . "','" . ' 36' . "','" . ' 37';
            break;

        default:
            $house = '???';
    }

    //query 조건
    //$sensorSerial = $_mysqli->real_escape_string( $sensorSerial );
    if ($sensorSerial) {
        $where = "sensorSerial IN( '{$sensorSerial}' ) ";
    }
    $where .= $date;

    //paging
    $offset = ( $current_page - 1 ) * $records_per_page;

    if ($sensorSerial) {
        $calcQuery = "
			SELECT MAX(val) AS max, MIN(val) AS min, AVG(val) AS avg
			FROM envdata
			WHERE {$where}
			";
        $query     = "SELECT * FROM envdata WHERE {$where} LIMIT $offset, $records_per_page ";
    } else {
        //DB 조회
        $query = "SELECT * FROM envdata ORDER BY serial desc limit 50";
    }


    if ($calcQuery) {
        $calc_result = $_mysqli->query( $calcQuery );
    }

    $db_result = $_mysqli->query( $query );


    //검색 발생시
    if ($calcQuery) {
        if (!$calc_result) {
            $code = 502;
            $msg  = "조회 중 오류가 발생했습니다.(code {$code})\n관리자에게 문의해 주세요.";
            throw new mysqli_sql_exception( $msg, $code );
        }
    }

    //DB 조회(검색x)
    if (!$db_result) {
        $code = 502;
        $msg  = "조회 중 오류가 발생했습니다.(code {$code})\n관리자에게 문의해 주세요.";
        throw new mysqli_sql_exception( $msg, $code );
    }

    //DB 데이터 행 조회
    $dbDataRows = $db_result->num_rows;


} catch (mysqli_sql_exception $e) {
    $arrRtn['code'] = $e->getCode();
    $arrRtn['msg']  = $e->getMessage();
    echo json_encode( $arrRtn );

} catch (Exception $e) {
    $arrRtn['code'] = $e->getCode();
    $arrRtn['msg']  = $e->getMessage();
    echo json_encode( $arrRtn );
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
        <!-- 검색 -->
        <div>
            <form id="envSearchForm" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <fieldset class="select_date">
                    <div class="">
                        <label for="month"><strong>기간</strong></label>
                        <select name="month" id="month">
                            <option value="0" selected>선택</option>
                            <option value="01">1월</option>
                            <option value="02">2월</option>
                            <option value="03">3월</option>
                            <option value="04">4월</option>
                            <option value="05">5월</option>
                            <option value="06">6월</option>
                            <option value="07">7월</option>
                            <option value="08">8월</option>
                            <option value="09">9월</option>
                            <option value="10">10월</option>
                            <option value="11">11월</option>
                            <option value="12">12월</option>
                        </select>
                    </div>
                </fieldset>
                <fieldset class="select_house">
                    <div class="">
                        <label for="house"><strong>하우스:</strong></label>
                        <select name="sensor" id="sensor">
                            <option value="0">선택</option>
                            <option value="38">이광재님</option>
                            <option value="39">김창열님</option>
                            <option value="appleTemp">사과하우스(온도)</option>
                            <option value="appleHumi1">사과하우스1(습도)</option>
                            <option value="appleHumi2">사과하우스2(습도)</option>
                            <option value="grapeHumi13">포도13동(습도)</option>
                            <option value="grapeHumi14">포도14동(습도)</option>
                            <option value="daecuHumi">대추하우스(습도)</option>
                            <option value="sanchoHumi">산초하우스(습도)</option>
                            <option value="asparagusHumi">아스파라거스(습도)</option>
                        </select>
                        <button style="margin-left: 1px;" type="button" onclick="searchEnv()"
                            class="btn btn-outline-primary" title="검색버튼" label="검색버튼">검색</button>
                        <button type="button" onclick="selectAll()" class="btn btn-outline-danger" title="리셋버튼"
                            label="검색버튼">리셋</button>
                    </div>
                </fieldset>
            </form>
        </div>
        <!-- .검색 -->
        <div>
            <?php if ($calc_result && $dbDataRows) {
                $calcData = $calc_result->fetch_array();
                $min      = $calcData['min'];
                $max      = $calcData['max'];
                $avg      = $calcData['avg'];
                $avg      = sprintf( '%0.2f', $avg );
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
                                <?php echo $house; ?>
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
                    // 리스트
                    if ($dbDataRows > 0) {
                        $no = 1;
                        while ( $envData = $db_result->fetch_array() ) {
                            //변수 정리
                            $envSerial = $envData['serial'];
                            $envSensor = $envData['sensorSerial'];
                            $envValue  = $envData['val'];
                            $envDate   = $envData['enterdate'];
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
    <script src="./assets/js/myScript.js" defer></script>