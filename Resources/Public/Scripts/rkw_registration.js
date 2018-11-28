var RKWregistration = RKWregistration|| {};

RKWregistration.Handle = (function ($) {

	var $layer;
    var $layerContent;
	var _layerId = 'rkw-registration';

	var _init = function(){
		$(document).ready(_onReady);
	};

	var _onReady = function(){
		$layer = $('#'+_layerId);
        $layerContent = $('#'+_layerId + '-target');
		if ($layerContent.length)
			_updateTarget();
	};

	var _updateTarget = function(){

		var $url = '/?type=1449722003&v=' + jQuery.now();

		// check for data attribute
		if ($layerContent.attr('data-url')) {
			if($layerContent.attr('data-url').indexOf('?') === -1){
				$url =  $layerContent.attr('data-url') + '?v=' + jQuery.now();
			} else {
				$url = $layerContent.attr('data-url') + '&v=' + jQuery.now();
			}
		}

		// check if there are params
		$.ajax({
			url: $url,
			data: {
				'tx_rkwregistration_rkwregistration[controller]': 'Ajax',
				'tx_rkwregistration_rkwregistration[action]': 'loginInfo'
			},
			success: function (data) {
				if (data.data){

					// replace html and execute contained JavaScript
                    $layerContent.html(data.data);
				}
			},
			dataType: 'json'
		});
	};


	/**
	 * Public interface
	 * @public
	 */
	return {
		init: _init,
		updateTarget: _updateTarget
	}

})(jQuery);

RKWregistration.Handle.init();
