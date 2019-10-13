/****************************************************************/
/* m_Completepayment  설명                                      */
/****************************************************************/
/* 인증완료시 재귀 함수                                         */
/* 해당 함수명은 절대 변경하면 안됩니다.                        */
/* 해당 함수의 위치는 payplus.js 보다먼저 선언되어여 합니다.    */
/* Web 방식의 경우 리턴 값이 form 으로 넘어옴                   */
/****************************************************************/
function m_Completepayment( FormOrJson, closeEvent ) 
{
    var frm = document.order_info; 
 
    /********************************************************************/
    /* FormOrJson은 가맹점 임의 활용 금지                               */
    /* frm 값에 FormOrJson 값이 설정 됨 frm 값으로 활용 하셔야 됩니다.  */
    /* FormOrJson 값을 활용 하시려면 기술지원팀으로 문의바랍니다.       */
    /********************************************************************/
    GetField( frm, FormOrJson ); 

    if( frm.res_cd.value == "0000" )
    {
        alert("결제 승인 요청 전,\n\n반드시 결제창에서 고객님이 결제 인증 완료 후\n\n리턴 받은 ordr_chk 와 업체 측 주문정보를\n\n다시 한번 검증 후 결제 승인 요청하시기 바랍니다."); //업체 연동 시 필수 확인 사항.
        /*
            가맹점 리턴값 처리 영역
        */
        frm.submit(); 
    }
    else
    {
        alert("[" + frm.res_cd.value + "] " + frm.res_msg.value);
        closeEvent();

        if(frm.res_cd.value == "3001") {
            if(window.opener && !window.opener.closed) {
                window.close();
                self.close();
                this.close();
            } else {
                window.location.href = "/";
            }
        }
    }
}
