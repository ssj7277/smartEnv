

function searchEnv() {
	var month = document.getElementById('month').value;
	var house = document.getElementById('sensor').value;

	if (house == '0') {
		alert('하우스를 선택하세요!!');
		return false;
	}
	if (month == '0') {
		alert('기간을 선택하세요!!');
		return false;
	}
	envSearchForm.submit();

	console.log(month);
}

function selectAll() {
	window.location.href = "http://192.168.50.5/html/smartenv/index.php";
}

// 상단으로
function scrollToTop() {
	window.scrollTo(0, 0);
}


// 하단으로
function scrollToBottom() {
	window.scrollTo(0, document.body.scrollHeight);
}
