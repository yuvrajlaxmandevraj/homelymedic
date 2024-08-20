<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1><?= labels('Cities', "Cities") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item">Cities</a></div>
            </div>
        </div>
        <div class="container-fluid card">

            <?= helper('form'); ?>
            <div class="row">
                <div class="col-md">
                    <h2 class='section-title'><?= labels('Add cities', "Add Cities") ?></h2>
                    <div class="card-body">
                        <form action="add-city" method="post" id="add-city">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="">Search your city</label>
                                    </div>
                                    <input id="search_places" class="form-control" type="text" name="places">
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="">Map</label>
                                    </div>
                                    <div id="map_wrapper_div">
                                        <div id="map"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="">Latitude</label>
                                        <input type="text" class="form-control" name="latitude" id="latitude" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="">Longitude</label>
                                        <input type="text" class="form-control" name="longitude" id="longitude" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="">City Name: </label>
                                        <input type="text" class="form-control" name="city_name" id="city_name" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="">Time To Travel: (km) </label>
                                        <input type="text" class="form-control" name="time_to_travel">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="">Maximum Deliverable Distance: (km) </label>
                                        <input type="text" class="form-control" name="maximum_delivrable_distance">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="">Delivery Charges Methods</label>
                                        <select class="form-control" name="delivery_charge_method" id="delivery_charge_method">
                                            <option value="">Select Method</option>
                                            <option value="fixed_charge">Fixed Delivery Charges</option>
                                            <option value="per_km_charge">Per KM Delivery Charges</option>
                                            <option value="range_wise_charges">Range Wise Delivery Charges</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 delivery_charge_method_result">

                                </div>
                                <div class="col-md-6 range_wise_km d-none">
                                    <div class="form-group col-sm-12" id="range_wise_charges_input" style="">
                                        <label for="range_wise_charges">Range Wise Delivery Charges <span class="text-danger text-sm">* </span> <span class="text-secondary text-sm">(Set Proper ranges for delivery charge. Do not repeat the range value to next range. For e.g. 1-3,4-6)</span> </label>

                                        <div class="form-group row">
                                            <div>1. </div>
                                            <div class="col-sm-2">
                                                <input type="number" class="form-control" name="from_range[]" id="from_range1" placeholder="From Range" min="0">
                                            </div>
                                            <div class="btn  btn-secondary">To</div>
                                            <div class="col-sm-2">
                                                <input type="number" class="form-control" name="to_range[]" id="to_range1" placeholder="To Range" min="0">
                                            </div>
                                            <div class="col-sm-4">
                                                <input type="number" class="form-control" name="price[]" id="price1" placeholder="Price" min="0">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div>2. </div>
                                            <div class="col-sm-2">
                                                <input type="number" class="form-control" name="from_range[]" id="from_range2" placeholder="From Range" min="0">
                                            </div>
                                            <div class="btn  btn-secondary">To</div>
                                            <div class="col-sm-2">
                                                <input type="number" class="form-control" name="to_range[]" id="to_range2" placeholder="To Range" min="0">
                                            </div>
                                            <div class="col-sm-4">
                                                <input type="number" class="form-control" name="price[]" id="price2" placeholder="Price" min="0">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div>3. </div>
                                            <div class="col-sm-2">
                                                <input type="number" class="form-control" name="from_range[]" id="from_range3" placeholder="From Range" min="0">
                                            </div>
                                            <div class="btn  btn-secondary">To</div>
                                            <div class="col-sm-2">
                                                <input type="number" class="form-control" name="to_range[]" id="to_range3" placeholder="To Range" min="0">
                                            </div>
                                            <div class="col-sm-4">
                                                <input type="number" class="form-control" name="price[]" id="price3" placeholder="Price" min="0">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div>4. </div>
                                            <div class="col-sm-2">
                                                <input type="number" class="form-control" name="from_range[]" id="from_range4" placeholder="From Range" min="0">
                                            </div>
                                            <div class="btn  btn-secondary">To</div>
                                            <div class="col-sm-2">
                                                <input type="number" class="form-control" name="to_range[]" id="to_range4" placeholder="To Range" min="0">
                                            </div>
                                            <div class="col-sm-4">
                                                <input type="number" class="form-control" name="price[]" id="price4" placeholder="Price" min="0">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div>5. </div>
                                            <div class="col-sm-2">
                                                <input type="number" class="form-control" name="from_range[]" id="from_range5" placeholder="From Range" min="0">
                                            </div>
                                            <div class="btn  btn-secondary">To</div>
                                            <div class="col-sm-2">
                                                <input type="number" class="form-control" name="to_range[]" id="to_range5" placeholder="To Range" min="0">
                                            </div>
                                            <div class="col-sm-4">
                                                <input type="number" class="form-control" name="price[]" id="price5" placeholder="Price" min="0">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div>6. </div>
                                            <div class="col-sm-2">
                                                <input type="number" class="form-control" name="from_range[]" id="from_range6" placeholder="From Range" min="0">
                                            </div>
                                            <div class="btn  btn-secondary">To</div>
                                            <div class="col-sm-2">
                                                <input type="number" class="form-control" name="to_range[]" id="to_range6" placeholder="To Range" min="0">
                                            </div>
                                            <div class="col-sm-4">
                                                <input type="number" class="form-control" name="price[]" id="price6" placeholder="Price" min="0">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div>7. </div>
                                            <div class="col-sm-2">
                                                <input type="number" class="form-control" name="from_range[]" id="from_range7" placeholder="From Range" min="0">
                                            </div>
                                            <div class="btn  btn-secondary">To</div>
                                            <div class="col-sm-2">
                                                <input type="number" class="form-control" name="to_range[]" id="to_range7" placeholder="To Range" min="0">
                                            </div>
                                            <div class="col-sm-4">
                                                <input type="number" class="form-control" name="price[]" id="price7" placeholder="Price" min="0">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div>8. </div>
                                            <div class="col-sm-2">
                                                <input type="number" class="form-control" name="from_range[]" id="from_range8" placeholder="From Range" min="0">
                                            </div>
                                            <div class="btn  btn-secondary">To</div>
                                            <div class="col-sm-2">
                                                <input type="number" class="form-control" name="to_range[]" id="to_range8" placeholder="To Range" min="0">
                                            </div>
                                            <div class="col-sm-4">
                                                <input type="number" class="form-control" name="price[]" id="price8" placeholder="Price" min="0">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div>9. </div>
                                            <div class="col-sm-2">
                                                <input type="number" class="form-control" name="from_range[]" id="from_range9" placeholder="From Range" min="0">
                                            </div>
                                            <div class="btn  btn-secondary">To</div>
                                            <div class="col-sm-2">
                                                <input type="number" class="form-control" name="to_range[]" id="to_range9" placeholder="To Range" min="0">
                                            </div>
                                            <div class="col-sm-4">
                                                <input type="number" class="form-control" name="price[]" id="price9" placeholder="Price" min="0">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div>10. </div>
                                            <div class="col-sm-2">
                                                <input type="number" class="form-control" name="from_range[]" id="from_range10" placeholder="From Range" min="0">
                                            </div>
                                            <div class="btn  btn-secondary">To</div>
                                            <div class="col-sm-2">
                                                <input type="number" class="form-control" name="to_range[]" id="to_range10" placeholder="To Range" min="0">
                                            </div>
                                            <div class="col-sm-4">
                                                <input type="number" class="form-control" name="price[]" id="price10" placeholder="Price" min="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <button class="btn btn-primary mx-2" id="btnAdd" type="submit">Add City</button>
                                <button class="btn btn-secondary" type="reset">Reset</button>
                            </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="container-fluid card">
            <h2 class='section-title'><?= labels('all_cities', "All Cities") ?></h2>
            <div class="row">
                <div class="col-lg">
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="col-md">
                                <table class="table " id="city_list" data-detail-formatter="user_formater" data-auto-refresh="true" data-toggle="table" data-url="<?= base_url("admin/city/list") ?>" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-search="true" data-show-columns="true" data-show-columns-search="true" data-show-refresh="true" data-sort-name="id" data-sort-order="desc">
                                    <thead>
                                        <tr>
                                            <th data-field="id" class="text-center" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                            <th data-field="name" class="text-center" data-sortable="true"><?= labels('name', 'name') ?></th>
                                            <th data-field="latitude" class="text-center" data-sortable="true"><?= labels('latitude', 'Latitude') ?></th>
                                            <th data-field="longitude" class="text-center" data-sortable="true"><?= labels('longitude', 'Longitude') ?></th>
                                            <th data-field="delivery_charge_method" class="text-center" data-sortable="true"><?= labels('delivery_charge_method', 'Delivery Charge Method') ?></th>
                                            <th data-field="fixed_charge" class="text-center" data-visible="false" data-sortable="true"><?= labels('fixed_charge', 'fixed_charge') ?></th>
                                            <th data-field="per_km_charge" class="text-center" data-visible="false" data-sortable="true"><?= labels('per_km_charge', 'Per Km Charge') ?></th>
                                            <th data-field="range_wise_charges" class="text-center" data-visible="false" data-sortable="true"><?= labels('range_wise_charges', 'Range wise charges') ?></th>
                                            <th data-field="max_deliverable_distance" class="text-center" data-visible="false" data-sortable="true"><?= labels('time_to_travel', 'Time to_travel') ?></th>
                                            <th data-field="created_at" class="text-center" data-sortable="true"><?= labels('created_at', 'Created At') ?></th>
                                            <th data-field="operations" class="text-center" data-events="City_events"><?= labels('operations', 'Operations') ?></th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- update modal -->

        <div class="card" id="city_update_modal">
            <div class="card-header d-flex justify-content-between">
                <h2 class='section-title'><?= labels('update_city', "Update City") ?></h2>
                <h2 class="float-right" id="close-div">
                    &times;
                </h2>
            </div>
            <div class="card-body">
                <form action="<?= base_url('admin/cities/edit_city') ?>" method="post" id="update_city" class='form-submit-event'>
                    <input type="hidden" name="id" id="id">
                    <div class="row">
                        <div class="col-md">
                            <div class="form-group">
                                <input id="search_places_u" class="form-control" type="text" name="name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="map_wrapper_div_u" id="map_wrapper_div_u">
                                <div class="map" id="map_u">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md">
                            <div class="form-group">
                                <label for="u_city_name">City Name</label>
                                <input id="u_city_name" class="form-control" type="text" name="name" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="u_latitude">Latitude</label>
                                <input id="u_latitude" class="form-control" type="text" name="latitude" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="u_longitude">Longitude</label>
                                <input id="u_longitude" class="form-control" type="text" name="longitude" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">Time To Travel: (km) </label>
                                <input type="text" class="form-control" name="time_to_travel" id="u_travel">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">Maximum Deliverable Distance: (km) </label>
                                <input type="text" class="form-control" name="maximum_delivrable_distance" id="u_maximum_delivrable_distance">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">Delivery Charges Methods</label>
                                <select class="form-control" name="delivery_charge_method" id="delivery_charge_method_u">
                                    <option value="">Select Method</option>
                                    <option value="fixed_charge">Fixed Delivery Charges</option>
                                    <option value="per_km_charge">Per KM Delivery Charges</option>
                                    <option value="range_wise_charges">Range Wise Delivery Charges</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 delivery_charge_method_result_u">

                        </div>
                        <div class="col-md-6  d-none" id="range_wise_km">
                            <div class="form-group col-sm-12" id="range_wise_charges_input" >
                                <label for="range_wise_charges">Range Wise Delivery Charges <span class="text-danger text-sm">* </span> <span class="text-secondary text-sm">(Set Proper ranges for delivery charge. Do not repeat the range value to next range. For e.g. 1-3,4-6)</span> </label>

                                <div class="form-group row">
                                    <div>1. </div>
                                    <div class="col-sm-2">
                                        <input type="number" class="form-control" name="from_range[]" id="from_range1" placeholder="From Range" min="0">
                                    </div>
                                    <div class="btn  btn-secondary">To</div>
                                    <div class="col-sm-2">
                                        <input type="number" class="form-control" name="to_range[]" id="to_range1" placeholder="To Range" min="0">
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="number" class="form-control" name="price[]" id="price1" placeholder="Price" min="0">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div>2. </div>
                                    <div class="col-sm-2">
                                        <input type="number" class="form-control" name="from_range[]" id="from_range2" placeholder="From Range" min="0">
                                    </div>
                                    <div class="btn  btn-secondary">To</div>
                                    <div class="col-sm-2">
                                        <input type="number" class="form-control" name="to_range[]" id="to_range2" placeholder="To Range" min="0">
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="number" class="form-control" name="price[]" id="price2" placeholder="Price" min="0">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div>3. </div>
                                    <div class="col-sm-2">
                                        <input type="number" class="form-control" name="from_range[]" id="from_range3" placeholder="From Range" min="0">
                                    </div>
                                    <div class="btn  btn-secondary">To</div>
                                    <div class="col-sm-2">
                                        <input type="number" class="form-control" name="to_range[]" id="to_range3" placeholder="To Range" min="0">
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="number" class="form-control" name="price[]" id="price3" placeholder="Price" min="0">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div>4. </div>
                                    <div class="col-sm-2">
                                        <input type="number" class="form-control" name="from_range[]" id="from_range4" placeholder="From Range" min="0">
                                    </div>
                                    <div class="btn  btn-secondary">To</div>
                                    <div class="col-sm-2">
                                        <input type="number" class="form-control" name="to_range[]" id="to_range4" placeholder="To Range" min="0">
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="number" class="form-control" name="price[]" id="price4" placeholder="Price" min="0">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div>5. </div>
                                    <div class="col-sm-2">
                                        <input type="number" class="form-control" name="from_range[]" id="from_range5" placeholder="From Range" min="0">
                                    </div>
                                    <div class="btn  btn-secondary">To</div>
                                    <div class="col-sm-2">
                                        <input type="number" class="form-control" name="to_range[]" id="to_range5" placeholder="To Range" min="0">
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="number" class="form-control" name="price[]" id="price5" placeholder="Price" min="0">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div>6. </div>
                                    <div class="col-sm-2">
                                        <input type="number" class="form-control" name="from_range[]" id="from_range6" placeholder="From Range" min="0">
                                    </div>
                                    <div class="btn  btn-secondary">To</div>
                                    <div class="col-sm-2">
                                        <input type="number" class="form-control" name="to_range[]" id="to_range6" placeholder="To Range" min="0">
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="number" class="form-control" name="price[]" id="price6" placeholder="Price" min="0">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div>7. </div>
                                    <div class="col-sm-2">
                                        <input type="number" class="form-control" name="from_range[]" id="from_range7" placeholder="From Range" min="0">
                                    </div>
                                    <div class="btn  btn-secondary">To</div>
                                    <div class="col-sm-2">
                                        <input type="number" class="form-control" name="to_range[]" id="to_range7" placeholder="To Range" min="0">
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="number" class="form-control" name="price[]" id="price7" placeholder="Price" min="0">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div>8. </div>
                                    <div class="col-sm-2">
                                        <input type="number" class="form-control" name="from_range[]" id="from_range8" placeholder="From Range" min="0">
                                    </div>
                                    <div class="btn  btn-secondary">To</div>
                                    <div class="col-sm-2">
                                        <input type="number" class="form-control" name="to_range[]" id="to_range8" placeholder="To Range" min="0">
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="number" class="form-control" name="price[]" id="price8" placeholder="Price" min="0">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div>9. </div>
                                    <div class="col-sm-2">
                                        <input type="number" class="form-control" name="from_range[]" id="from_range9" placeholder="From Range" min="0">
                                    </div>
                                    <div class="btn  btn-secondary">To</div>
                                    <div class="col-sm-2">
                                        <input type="number" class="form-control" name="to_range[]" id="to_range9" placeholder="To Range" min="0">
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="number" class="form-control" name="price[]" id="price9" placeholder="Price" min="0">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div>10. </div>
                                    <div class="col-sm-2">
                                        <input type="number" class="form-control" name="from_range[]" id="from_range10" placeholder="From Range" min="0">
                                    </div>
                                    <div class="btn  btn-secondary">To</div>
                                    <div class="col-sm-2">
                                        <input type="number" class="form-control" name="to_range[]" id="to_range10" placeholder="To Range" min="0">
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="number" class="form-control" name="price[]" id="price10" placeholder="Price" min="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success" id="update">Update </button>
                </form>
            </div>
        </div>


    </section>
</div>
<?php
$api_key = get_settings('api_key_settings', true);
?>
<style>
    .container {
        padding: 2%;
        text-align: center;

    }

    #map_wrapper_div {
        height: 400px;
    }

    #map_wrapper_div_u {
        height: 400px;
    }

    #map {
        width: 100%;
        height: 100%;
    }

    #map_u {
        width: 100%;
        height: 100%;
    }
    #close-div{
        cursor: pointer;
    }
    #close-div:hover{
        color: red;
    }
</style>

<script>
    let autocomplete_city;
    let map;

    function initautocomplete() {

        autocomplete_city = new google.maps.places.Autocomplete(
            document.getElementById('search_places'), {
                types: ['locality'],
                componentRestriction: {
                    'country': ['USA']
                },
                fields: ['place_id', 'geometry', 'name'],
            }
        )

        autocomplete_city.addListener('place_changed', onPlaceChanged);

        var place = autocomplete_city.getPlace();
        var latitude = typeof(place) != "undefined" ? place.geometry.location.lat() : parseFloat("23.242697188102483");
        var longitude = typeof(place) != "undefined" ? place.geometry.location.lng() : parseFloat("69.6639650758625");
        var name = typeof(place) != "undefined" ? place.geometry.location.lng() : 'Bhuj';

        center = {
            lat: latitude,
            lng: longitude
        };
        var map_location = document.getElementById("map");
        map = new google.maps.Map(map_location, {
            center,
            zoom: 8,
        });
        function onPlaceChanged() {
            place = autocomplete_city.getPlace();
            let contentString =
                "<h6> " + place.name + " </h6>";
            center = {
                lat: place.geometry.location.lat(),
                lng: place.geometry.location.lng()
            };
            const infowindow = new google.maps.InfoWindow({
                content: contentString,
            });
            map = new google.maps.Map(map_location, {
                center,
                zoom: 10,
            });
            const marker = new google.maps.Marker({
                title: place.name,
                animation: google.maps.Animation.DROP,
                position: center,
                map: map,
            });
            marker.addListener("click", () => {
                infowindow.open({
                    anchor: marker,
                    map,
                    shouldFocus: false,
                });
            });

            $('#latitude').val(latitude);
            $('#longitude').val(longitude);
            $('#city_name').val(place.name);
            console.log(latitude);
            console.log(longitude);
        }

    }
    window.initMap = initautocomplete;
</script>