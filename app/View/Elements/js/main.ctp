<?php if (!$this->request->is('ajax')): ?>
    <script>
        var halovn = {
            api: {
                detectRegion: "<?php echo $this->Html->url(array('controller' => 'Weather', 'action' => 'getByRegion')) ?>"
            },
            extra_params: {
                lat: '',
                lng: '',
                user_region_id: '5555bee2c4a1608e6e9a1610',
                lang: 'vi'
            },
            extra_param_clss: '.extra_param',
            recent_history_id: '#recent_history',
            redirect_location: '',
            online: 1,
            max_recent_history: 5,
            location_expried: 60 * 60 * 1000, // thời gian hết hạn của việc lưu trữ location
            region_expried: 15 * 60 * 1000
        };

        halovn.serializeQueryString = function (obj, prefix) {
            var str = [];
            for (var p in obj) {
                if (obj.hasOwnProperty(p)) {
                    var k = prefix ? prefix + "[" + p + "]" : p, v = obj[p];
                    str.push(typeof v == "object" ?
                            serialize(v, k) :
                            encodeURIComponent(k) + "=" + encodeURIComponent(v));
                }
            }
            return str.join("&");
        };

        // detect region_id hiện tại của visitor dựa vào lat, lng
        halovn.getRegion = function (geo) {

            var self = this;
            if (!geo.hasOwnProperty('user_region_id')) {

                geo.user_region_id = '';
            }
            var req = $.get(this.api.detectRegion, {lat: geo.lat, lng: geo.lng, user_region_id: geo.user_region_id}, function (data) {

                if (data.status === 'success') {

                    self.extra_params.user_region_id = data.data.region.id;
                    geo.user_region_id = data.data.region.id;

                    // lấy lại thông tin của region
                    geo.region = data.data.region;

                    // lưu lại thời tiết
                    geo.weather_current = data.data.current;
                    geo.weather_forecast = data.data.arr_weather;

                    // bắt ra sự kiện
                    $(document).trigger('halovnDetectRegion', geo);

                    halovn.appendExtraParmas();

                    // thực hiện lưu lại vị trí location
                    localforage.setItem('halovn_location', geo, function (err, value) {

                        console.log('Geolocation was set into client successful.');
                        console.log('error:');
                        console.log(err);
                        console.log('value: ');
                        console.log(value);

                        halovn.refresh();
                    });

                } else {

                    halovn.refresh();
                }
            }, 'json');

            req.fail(function (jqXHR, textStatus, errorThrown) {

                console.log('Call api: ' + halovn.api.detectRegion + ' was failed');
                console.log(textStatus);
                console.log(errorThrown);
            });
        };

        // thực hiện refresh lại trang page khi lấy về được location
        halovn.refresh = function () {

            var param_string = halovn.serializeQueryString(halovn.extra_params);
            var refresh_url = '';
            if (!halovn.redirect_location) {

                var current_url = window.location.href;
                refresh_url = current_url + ((current_url.indexOf('?') != -1) ? '&' : '?') + param_string;
            } else {

                refresh_url = halovn.redirect_location + ((halovn.redirect_location.indexOf('?') != -1) ? '&' : '?') + param_string;
            }
            console.log('refresh_url: ' + refresh_url);
            window.location.replace(refresh_url);
        };

        // lấy về lat, lng của vị trí hiện tại visitor
        halovn.getLocation = function () {

            if (navigator.geolocation) {

                console.log("Geolocation is dectecting...");
                navigator.geolocation.getCurrentPosition(this.successGetPosition, this.errorGetPosition);
            } else {

                console.log("Geolocation is not supported by this browser.");
            }
        };

        // callback khi lấy về lat, lng vị trí hiện tại của visitor thành công
        halovn.successGetPosition = function (position) {

            console.log('Geolocation was get successful');
            var geo = {
                lat: position.coords.latitude,
                lng: position.coords.longitude,
                expried: new Date().getTime() + halovn.location_expried,
                user_region_id: halovn.extra_params.user_region_id
            };
            halovn.extra_params.lat = geo.lat;
            halovn.extra_params.lng = geo.lng;

            halovn.appendExtraParmas();

            // thực hiện lưu lại vị trí location
            localforage.setItem('halovn_location', geo, function (err, value) {

                console.log('Geolocation was set into client successful.');
                console.log('error:');
                console.log(err);
                console.log('value: ');
                console.log(value);
            });

            // request lên server để lấy ra region
            halovn.getRegion(geo);
        };

        // callback khi lấy về lat, lng vị trí hiện tại của visitor thất bại
        halovn.errorGetPosition = function (error) {

            console.log("Geolocation is not get.");
            console.log(error);

//            $('#alert-modal-body').html('Bạn cần bật chức năng định vị GPS');
//            $('#alert-modal').modal('show');
        };

        // tự động điền thêm các tham số lng, lat, user_region_id vào thẻ <a> có class là extra_param
        // ngay khi lấy được xong lat, lng của visitor
        halovn.appendExtraParmas = function () {

            var $extra_param_el = $(halovn.extra_param_clss);
            var param_string = halovn.serializeQueryString(halovn.extra_params);
            $extra_param_el.each(function () {

                $(this).attr('href', this.href + ((this.href.indexOf('?') != -1) ? '&' : '?') + param_string);
            });
        };

        // lấy ra lat,lng của visitor từ local client, có thực hiện set hết hạn
        halovn.getLocationItem = function (key) {

            localforage.getItem(key, function (err, value) {

                // nếu không tồn tại giá trị, thực hiện lấy lại
                if (!value) {

                    halovn.getLocation();
                    return;
                } else {

                    console.log('Location was existed');
                    console.log(value);
                    if (!value.expried) {

                        value.expried = 0;
                    }

                    // kiểm tra xem dữ liệu đã hết hạn chưa? nếu hết hạn thì thực hiện xóa và lấy lại
                    if (new Date().getTime() > value.expried) {

                        console.log('Reset location, detect location again.');
                        localforage.removeItem(key);
                        halovn.getLocation();
                        return;
                    }

                    halovn.extra_params.lat = value.lat;
                    halovn.extra_params.lng = value.lng;

//                    if (!halovn.extra_params.lat || !halovn.extra_params.lng) {
//
//                        $('#alert-modal-body').html('Bạn cần bật chức năng định vị GPS');
//                        $('#alert-modal').modal('show');
//                        return false;
//                    }

                    // bắt ra sự kiện
                    $(document).trigger('halovnDetectRegion', value);

                    if (value.user_region_id) {

                        halovn.extra_params.user_region_id = value.user_region_id;
                    }

                    halovn.appendExtraParmas();
                }
            });
        };

        // lưu trữ dành cho tính năng xem gần đây
        halovn.saveRecentHistory = function (type, object) {

            var key = 'halovn_recent_history_' + type;
            localforage.getItem(key, function (err, value) {

                object.created = new Date().getTime();
                if (!value) {

                    var data = [object];
                    localforage.setItem(key, data, function (err, value) {

                        console.log('saveRecentHistory add new successful!');
                        console.log('value: ');
                        console.log(value);
                        console.log(key + ': ');
                        console.log(data);
                    });
                } else {

                    var data = value;
                    // kiểm tra xem object đã được chứa trong recent_history chưa?
                    var check_exist = halovn.containsObjectId(object, data);
                    if (check_exist !== false) {

                        data[check_exist] = object;
                    } else {

                        data.unshift(object);
                    }

                    // thực hiện giới hạn số lượng lưu trong recent_history
                    data = halovn.sortRecentHistory(data);
                    data = data.slice(0, halovn.max_recent_history);

                    localforage.setItem(key, data, function (err, value) {

                        console.log('saveRecentHistory save successful!');
                        console.log('value: ');
                        console.log(value);
                        console.log(key + ': ');
                        console.log(data);
                    });
                }
            });
        };

        halovn.containsObjectId = function (obj, list) {
            var i;
            for (i = 0; i < list.length; i++) {
                if (list[i].id === obj.id) {
                    return i;
                }
            }

            return false;
        };

        // lấy ra recent_history
        halovn.getRecentHistory = function (type) {

            var key = 'halovn_recent_history_' + type;
            localforage.getItem(key, function (err, value) {

                console.log('getRecentHistory get successful!');
                console.log(key + ': ');
                console.log(value);
                console.log('prepare render RecentHistory');
                halovn.renderRecentHistory(value);
            });
        };

        // thực hiện render cho recent_history
        halovn.renderRecentHistory = function (list) {

            if (!list || !$.isArray(list)) {

                console.log('renderRecentHistory was hidden');
                $(halovn.recent_history_id).hide();
                return false;
            }

            // sắp xếp lại recent_history theo created DESC
            list = this.sortRecentHistory(list);
            var li_pattern = '<li><a href="{0}"><h3 class="recent-title">{1}</h3><p class="recently-location-detail recent-address">{2}</p></li>';
            var li_html = '';
            var $ul = $(halovn.recent_history_id).find('ul.recently-location-list');
            $ul.html('');
            $.each(list, function (index, value) {

                if (!value.hasOwnProperty('address')) {

                    value.address = '';
                }
                var li = li_pattern.replace('{0}', value.url).replace('{1}', value.name).replace('{2}', value.address);
                li_html += li;
            });
            $ul.html(li_html);

            $('.recent-title').quickfit({truncate: true, min: 17});
            $('.recently-location-detail').quickfit({truncate: true, min: 16});
            $('.recent-address').quickfit({truncate: true, min: 15});
        };

        // thực hiện sắp xếp lại recent_history theo created DESC
        halovn.sortRecentHistory = function (list) {

            return list.sort(this.compareRecentHistory);
        };

        halovn.compareRecentHistory = function (a, b) {
            if (a.created > b.created) {
                return -1;
            }
            if (a.created < b.created) {
                return 1;
            }
            return 0;
        };

        halovn.changeRegion = function (region_id) {

        };

        halovn.renderNavWeather = function (geo) {

            $('.location-text').text(geo.region.name);
            $('.banner-menu-icon').attr('src', geo.weather_current.icon);
            $('.banner-menu-celsius').text(geo.weather_current.temperature + '°C');
            $('.banner-menu-weather-info').text(geo.weather_current.content);
        };

        $(function () {

            FastClick.attach(document.body);

            $('.object-title').quickfit({truncate: true, min: 17});
            $('.object-address').quickfit({truncate: true, min: 15});

            $('.navbar-toggle').on('click', function () {

                localforage.getItem('halovn_location', function (err, value) {

                    console.log('Check whether location was set or not?');
                    if (!value) {

                        console.log('Get back location again.');
                        halovn.getRegion({lat: '', lng: '', expried: new Date().getTime() + halovn.location_expried});
                        return false;
                    }

                    // thực hiện render weather
                    halovn.renderNavWeather(value);
                });
            });

            halovn.getLocationItem('halovn_location');

            $('.nearby').on('click', function () {

                // khi detect visitor không bật location
                if (!halovn.extra_params.lat || !halovn.extra_params.lng) {

                    $('#alert-modal-body').html('Bạn cần bật chức năng định vị GPS');
                    $('#alert-modal').modal('show');
                    return false;
                }
            });

            $('#alert-modal').on('hidden.bs.modal', function (e) {

                var redirect = $(this).data('redirect');
                if (redirect) {

                    $(this).data('redirect', '');
                    window.location.replace(redirect);
                }
            });

            $('.keyword-form').on('submit', function () {

                if (!$(this).find('input[name="keyword"]').val().length) {

                    $('#alert-modal-body').html('Bạn phải nhập từ khóa tìm kiếm');
                    $('#alert-modal').modal('show');
                    return false;
                }
            });

            window.addEventListener("resize", function () {

                console.log('Resize .........');
                $('.recent-title').quickfit({truncate: true, min: 17});
                $('.recent-address').quickfit({truncate: true, min: 15});
                $('.object-title').quickfit({truncate: true, min: 17});
                $('.object-address').quickfit({truncate: true, min: 15});
            }, false);

            Offline.on('down', function () {

                console.log('Network go offline.');
                halovn.online = 0;
            });

            Offline.on('up', function () {

                console.log('Network go online.');
                if (!halovn.online) {

                    localforage.removeItem('halovn_location', function (err) {

                        console.log('halovn_location is cleared!');
                        console.log('Get back location ...');
                        halovn.getLocationItem('halovn_location');
                    });
                }
                halovn.online = 1;
            });
        });
    </script>
<?php endif; ?>