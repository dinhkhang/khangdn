<script type="text/javascript">

    function choicePackage(p) {
        if (p == 'G1') {
            if (confirm('Bạn muốn đăng ký gói G1 giá 2000đ/Ngày không?')) {
                window.location.href = "/players/registpackage?type=g1";
            }
        } else if (p == 'G7') {
            if (confirm('Bạn muốn đăng ký gói G7 giá 9000đ/Tuần không?')) {
                window.location.href = "/players/registpackage?type=g7";
            }
        }
    }
</script>

<div style="padding-top: 2em; display: block;" class="col s12" id="page1">			
    <div class="col s12">
        <div class="row">
            <div style="text-align:center;" class="col s12">
                <div class="col s6">
                    <div class="card">
                        <span class="card-title">Gói ngày</span>
                        <div class="card-content ">

                            <p class="pag-title">G1</p><p>
                                <small class="price-title">2000đ/Ngày</small>
                            </p><p><a href="javascript:choicePackage('G1')" class="waves-effect waves-light btn white-text modal-trigger">Chọn</a></p>
                        </div>
                    </div>
                </div>
                <div class="col s6">
                    <div class="card">
                        <span class="card-title">Gói tuần</span>
                        <div class="card-content ">

                            <p class="pag-title">G7</p>
                            <small class="price-title">9000đ/Tuần</small>								
                            <p><a href="javascript:choicePackage('G7')" class="waves-effect waves-light btn white-text modal-trigger">Chọn</a></p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>