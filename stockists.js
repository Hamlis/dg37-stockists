/**
 * Created by nermnv on 3/18/14.
 */

(function ($) {
    // Australia
    var defaultPosition = new google.maps.LatLng(-26.4425664, 133.281323),
        markers = [],
        infowindow,
        mapOptions = {
            zoom: 4
        },
        geocoder = new google.maps.Geocoder(),
        map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions),
        storesListUl = $('.category-list'),
        $btnSearch = $("#btnSearch").bind('click', function (event) {
            event.preventDefault();
            codeAddress($('#txtSearch').val(), sortStores);
        });

    function sortStores(point) {
        if (!point) {
            console.log('search geocode failed');
            return;
        }
        storesListUl.find('li').sort(function (a, b) {
            var d1 = google.maps.geometry.spherical.computeDistanceBetween(
                    point,
                    $(a).data('marker').getPosition()
                ),
                d2 = google.maps.geometry.spherical.computeDistanceBetween(
                    point,
                    $(b).data('marker').getPosition()
                );
            return d1 > d2 ? 1 : -1;
        }).appendTo(storesListUl)
            .first().find('a').trigger('click');
    }

    $('#txtSearch').bind('keypress', function (event) {
        if (event.keyCode == 13) {
            $btnSearch.trigger("click");
            event.preventDefault();
        }
    });

    function getInfoWindow(position, content) {
        if (!infowindow) {
            infowindow = new google.maps.InfoWindow({
                map: map
            });
        }
        infowindow.setPosition(position);
        infowindow.setContent(content);
        return infowindow;
    }

    $.getJSON("http://dg37.saldainiukai.hol.es/json.php", buildData);

    // Try HTML5 geolocation
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
            var currentPos = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
            map.setCenter(currentPos);
            setTimeout(function () {
                sortStores(currentPos);
            }, 2000);
        }, function () {
            handleNoGeolocation(true);
        });
    } else {
        // Browser doesn't support Geolocation
        handleNoGeolocation(false);
    }

    function handleNoGeolocation(errorFlag) {
        var content = 'Error: Your browser doesn\'t support geolocation.';
        if (errorFlag) {
            content = 'Error: The Geolocation service failed.';
        }

        var options = {
            map: map,
            // Australia
            position: new google.maps.LatLng(-26.4425664, 133.281323),
            content: content
        };

//            var infowindow = new google.maps.InfoWindow(options);
        map.setCenter(options.position);
    }

    function buildData(data) {
        if (!data) {
            return;
        }
        $.each(data.Stockist, function () {
            var position, marker,
                addMarkerAtPosition = $.proxy(function (position) {
                    var data = this,
                        lines = [],
                        titleStyle = ' style="font-family:\'Signika Negative\',sans-serif;'
                            + 'font-weight:bold;font-size:16px;line-height: 24px;"';

                    if (data.url) {
                        lines.push('<a' + titleStyle + ' href="' + data.url + '">' + data.title + '</a>');
                    } else {
                        lines.push('<span' + titleStyle + '>' + data.title + '</span>');
                    }
                    if (data.address) {
                        lines.push(data.address);
                    }
                    if (data.phone) {
                        lines.push(data.phone);
                    }
                    if (data.url) {
                        lines.push('<a href="' + data.url + '">' + data.url + '</a>');
                    }
                    lines.push('<br/><img src="http://dg37.saldainiukai.hol.es/pillow_corner.jpg" width="200" />');
                    if (!position) {
                        position = defaultPosition;
                    }
                    marker = new google.maps.Marker({
                        map: map,
                        position: position,
                        title: this.title
                    });
                    markers.push(marker);

                    google.maps.event.addListener(marker, 'click', function () {
                        var content =
                            "<p>" +
                                lines.join("<br/>") +
                                "</p>";
                        getInfoWindow(marker.getPosition(), content).open(marker.get('map'), marker);
                    });

                    storesListUl.append(
                        $("<li></li>", {
                            html: $("<a/>", {
                                text: this.title,
                                href: "#",
                                onclick: function (event) {
                                    map.setCenter(marker.getPosition());
                                    map.setZoom(14);
                                    google.maps.event.trigger(marker, 'click');
                                    event.preventDefault();
                                }
                            }),
                            data: {
                                Stockist: this,
                                marker: marker
                            }
                        })
                    );
                }, this);
            if (!this.address) {
                addMarkerAtPosition();
                return;
            }
            position = codeAddress(this.address, addMarkerAtPosition);
        });
    }

    function codeAddress(address, callback) {
        geocoder.geocode({ 'address': address}, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                callback(results[0].geometry.location);
                return;
            }
            callback(null);
        });
    }
})(jQuery);