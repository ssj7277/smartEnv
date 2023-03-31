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
    $pages_per_block  = 5;
    $current_page     = isset( $_GET['page'] ) ? $_GET['page'] : 1;

    //검색어
    $sensor = isset( $_GET['sensor'] ) ? $_GET['sensor'] : '';
    $month  = isset( $_GET['month'] ) ? $_GET['month'] : 0;

    //변수 초기화
    $where         = '';
    $date          = '';
    $total_pages   = '';
    $total_records = '';
    $sensorSerial  = '';

    $enterdate = '2023-';

    //날짜 지정
    if ($month) {
        $enterdate = $enterdate . $month;
        $date      = "AND enterdate LIKE '{$enterdate}%' ";
    }

    //농가 지정
    switch ($sensor) {
        case 38:
            $house = '이광재 하우스';
            $sensorSerial = '38';
            break;
        case 39:
            $house = '김창열 하우스';
            $sensorSerial = '39';
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

    if ($sensorSerial) {
        $where = "WHERE sensorSerial IN( '{$sensorSerial}' ) ";
    } else
        $where = "WHERE sensorSerial IN(38) ";
    $where .= $date;

    //paging query
    $count_query   = "SELECT COUNT(*) AS count FROM envdata $where";
    $query_result  = $_mysqli->query( $count_query );
    $count_result  = $query_result->fetch_assoc();
    $total_records = $count_result['count'];
    //echo $total_records;

    //paging block
    $current_block = ceil( $current_page / $records_per_page );
    $start_page    = ( $current_block - 1 ) * $pages_per_block + 1;
    $end_page      = min( $start_page + $pages_per_block - 1, $total_pages );



    $offset       = ( $current_page - 1 ) * $records_per_page;
    $query        = "SELECT serial, enterdate, sensorSerial, val FROM envdata $where LIMIT $offset, $records_per_page ";
    $query_result = $_mysqli->query( $query );
    //$select_db    = $query_result->fetch_assoc();

    $total_pages = ceil( $total_records / $records_per_page );
    $prev_page   = $current_page - 1;
    $next_page   = $current_page + 1;

    //DB 조회(검색x)
    if (!$count_result && $query_result) {
        $code = 502;
        $msg  = "조회 중 오류가 발생했습니다.(code {$code})\n관리자에게 문의해 주세요.";
        throw new mysqli_sql_exception( $msg, $code );
    }

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

        table {
            border: 1px solid;
            text-align: center;
            width: 650px;
        }

        th {
            border: 1px solid;
        }

        thead {
            background-color: springgreen;
            font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;
            height: 50px;
        }

        tbody {}

        td {
            border: 1px solid;
            padding: 10px;
            font-family: Arial, sans-serif;
        }

        .pagination li:not(:last-child) {
            margin-right: 5px;
        }

        .selectSize {
            width: 240px;
            margin-bottom: 10px;
            display: inline;
            font-size: 20px;
        }

        .labelMonth {
            color: mediumslateblue;
            font-size: 30px;
        }

        .labelHouse {
            color: midnightblue;
            font-size: 30px;
        }

        #topMove {
            margin-top: 10px;
            margin-left: 590px;
        }

        .inline {
            display: inline;
        }

        .tum {
            margin-left: 170px;
        }
        </style>
        <title>.</title>

    <body style="min-width:700px;">
        <h1>envData</h1><br />
        <!-- 검색 -->
        <div class="">
            <form id="envSearchForm" method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <fieldset class="select_date">
                    <div class="">
                        <label class="labelMonth">날짜&nbsp;&nbsp;&nbsp;</label>
                        <select class="form-select selectSize" name="month" id="month">
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
                        <label class="labelHouse">하우스</label>
                        <select class="form-select selectSize" name="sensor" id="sensor">
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
                        <button style="margin-left: 95px;" type="button" onclick="searchEnv()"
                            class="btn btn-outline-primary " title="검색버튼" label="검색버튼">검색</button>
                        <button type="button" onclick="selectAll()" class="btn btn-outline-danger" title="리셋버튼"
                            label="검색버튼">리셋</button>
                        <button type="button" onclick="scrollToBottom()" class="btn btn-outline-dark" title="하단 이동"
                            label="하단 이동">하단으로</button>
                    </div>

                </fieldset>
            </form>
        </div>
        <!-- .검색 -->
        <!-- calc data -->

        <!-- .calc data -->
        <div>
            <div style="margin-bottom: 10px;">
                <div class=''>
                    <!-- paging -->
                    <ul class='pagination'>
                        <?php
                        //paging block
                        $total_pages   = ceil( $total_records / $records_per_page );
                        $current_block = ceil( $current_page / 5 ); // 현재 블록 계산
                        $start_page    = ( $current_block - 1 ) * 5 + 1; // 시작 페이지 계산
                        $end_page      = min( $start_page + 4, $total_pages ); // 마지막 페이지 계산
                        
                        if ($current_block > 1) {
                            $prev_block = ( $current_block - 2 ) * 5 + 5;
                            echo "<li><a href='index.php?page=$prev_block&sensor=$sensor&month=$month'><button type='button' class='btn btn-outline-primary'>이전</button></a></li>";
                        }
                        for ( $i = $start_page; $i <= $end_page; $i++ ) {
                            if ($i == $current_page) {
                                echo "<li class='active'><button type='button' class='btn btn-success' disabled>$i</button></li>";
                            } else {
                                echo "<li><a href='?page=$i&sensor=$sensor&month=$month'><button type='button' class='btn btn-outline-secondary'>$i</button></a></li>";
                            }
                        }

                        if ($current_block < ceil( $total_pages / 5 )) {
                            $next_page = $end_page + 1;
                            echo "<li><a href='index.php?page=$next_page&sensor=$sensor&month=$month'><button type='button' class='btn btn-outline-primary'>다음</button></a></li>";
                        }
                        ?>
                    </ul>
                </div>
            </div>
            <table>
                <colgroup>
                </colgroup>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>데이터 번호</th>
                        <th>농가번호</th>
                        <th>값</th>
                        <th>날짜</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // 리스트
                    if ($total_records > 0) {
                        $no = $offset + 1;
                        while ( $select_db = $query_result->fetch_assoc() ) {
                            //변수 정리
                            $envSerial = $select_db['serial'];
                            $envSensor = $select_db['sensorSerial'];
                            $envValue  = $select_db['val'];
                            $envDate   = $select_db['enterdate'];
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
        <button type="button" onclick="scrollToTop()" class="btn btn-outline-dark" title="상단 이동" label="상단 이동"
            id="topMove">위로</button>
        <footer style="margin: 5em;">
        </footer>
    </body>

    </html>
    <script src="./assets/js/myScript.js" defer></script>