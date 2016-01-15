<div class="form-soi-cau">
	<form action="javascript:void(0)" method="post">
		<div class="pt-20 pt-m-100">
			<h1 class="text-do-ve">Dò vé số</h1>
		</div>
		<div class="pt-50"><input id="datepicker-soi-cau" value="2016-01-05" type="text"></div>
		<div class="pt-30 pt-m-50">
			<select class="selectpicker" name="c">
				<option value="XSAG">An Giang</option>
				<option value="XSBD">Bình Dương</option>
				<option value="XSBP">Bình Phước</option>
				<option value="XSBTH">Bình Thuận</option>
				<option value="XSBDH">Bình Định </option>
				<option value="XSBL">Bạc Liêu</option>
				<option value="XSBTR">Bến Tre</option>
				<option value="XSCM">Cà Mau</option>
				<option value="XSCT">Cần Thơ</option>
				<option value="XSDLK">DakLak</option>
				<option value="XSGL">Gia Lai</option>
				<option value="XSHG">Hậu Giang</option>
				<option value="XSHCM">Hồ Chí Minh</option>
				<option value="XSKH">Khánh Hòa</option>
				<option value="XSKG">Kiên Giang</option>
				<option value="XSKT">Kon Tum</option>
				<option value="XSLA">Long An</option>
				<option value="XSTD">Miền Bắc</option>
				<option value="XSNT">Ninh Thuận</option>
				<option value="XSPY">Phú Yên</option>
				<option value="XSQB">Quảng Bình</option>
				<option value="XSQNM">Quảng Nam</option>
				<option value="XSQNI">Quảng Ngãi</option>
				<option value="XSQT">Quảng Trị</option>
				<option value="XSST">Sóc Trăng</option>
				<option value="XSTTH">ThừaThiênHuế</option>
				<option value="XSTG">Tiền Giang</option>
				<option value="XSTV">Trà Vinh</option>
				<option value="XSTN">Tây Ninh</option>
				<option value="XSVL">Vĩnh Long</option>
				<option value="XSVT">Vũng Tàu</option>
				<option value="XSDL">Đà Lạt</option>
				<option value="XSDNG">Đà Nẵng</option>
				<option value="XSDNO">Đắc Nông</option>
				<option value="XSDN">Đồng Nai</option>
				<option value="XSDT">Đồng Tháp</option>
			</select>
		</div>
		<div class="pt-20 icon-do-ve"><i class="fa fa-compass"></i></div>
		<div class="pt-50 pt-m-50">
			<input type="text" class="date-chooser datepicker" placeholder="Nhập số" name="s">
		</div>
		<div class="pt-30 pt-m-50">
			<button type="submit" class="search-button">Xem Kết Quả</button>
		</div>
	</form>
	<div class="clear-fix"></div>
</div>
<div class="clear-fix"></div>
<h2 class="gio-mo-thuong">
	Giờ mở thưởng XSMB hôm nay còn:
</h2>

<!--time count down-->
<div class="clock"></div>
<div class="message"></div>
<!--#time count down-->

<div class="inputThongtin">

	<div class="box_kqxs box_cc" id="kqmienbac">
		<div id="kqxsmb">
			<div class="result-header">
				<h2><a href="#" title="kết quả xố số miền bắc">KẾT QUẢ XỔ SỐ MIỀN BẮC {XSMB} Ngày 08/01/2016</a></h2>
				<div class="div-toolbar">
					<span class="toolbar-i"><a href="#"><i class="fa fa-print"></i></a></span>
					<span class="toolbar-i"><a href="#"><i class="fa fa-angle-left"></i></a></span>
					<span class="toolbar-i"><a href="#"><i class="fa fa-angle-right"></i></a></span>
				</div>

			</div>
			<div class="box_so">
				<?= $this->element('table/norland'); ?>
				<?= $this->element('table/loto'); ?>
			</div>
		</div>
	</div>

	<div class="clearfix"></div>

	<!--////-->

	<div class="box_kqxs box_cc" id="kqmientrung">
		<div id="kqxsmb">
			<div class="result-header">
				<h2><a href="#" title="kết quả xố số miền bắc">KẾT QUẢ XỔ SỐ MIỀN TRUNG {XSMT} Ngày 08/01/2016</a></h2>
				<div class="div-toolbar">
					<span class="toolbar-i"><a href="#"><i class="fa fa-print"></i></a></span>
					<span class="toolbar-i"><a href="#"><i class="fa fa-angle-left"></i></a></span>
					<span class="toolbar-i"><a href="#"><i class="fa fa-angle-right"></i></a></span>
				</div>

			</div>
			<!--////-->
			<div class="box_so">
				<?= $this->element('table/southward'); ?>
				<?= $this->element('table/loto'); ?>
			</div>
			<!--///-->

			<!--////-->
			<div class="box_so margin-top-box">
				<?= $this->element('table/southward'); ?>
				<?= $this->element('table/loto'); ?>
			</div>
			<!--///-->
		</div>
	</div>
	<!--///-->
</div>
<?= $this->element('blogspost'); ?>