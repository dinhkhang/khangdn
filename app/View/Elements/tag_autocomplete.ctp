<?php
if (empty($url)) {

    $url = $this->Html->url(array('controller' => 'Tags', 'action' => 'reqSearch'));
}
?>
<script>
    $(function () {

        //autocomplete
        function log(message) {
            $("<div>").text(message).prependTo("#log");
            $("#log").scrollTop(0);
        }

        $(".tag-autocomplete").autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: "<?php echo $url ?>",
                    dataType: "json",
                    data: {
                        keyword: request.term,
                        type: '<?php echo!empty($object_type) ? $object_type : '' ?>'
                    },
                    success: function (data) {
                        response(data);
                    }
                });
            },
            minLength: 3,
            select: function (event, ui) {
                log(ui.item ?
                        "Selected: " + ui.item.label :
                        "Nothing selected, input was " + this.value);
            },
            open: function () {
                $(this).removeClass("ui-corner-all").addClass("ui-corner-top");
            },
            close: function () {
                $(this).removeClass("ui-corner-top").addClass("ui-corner-all");
            }
        });
    });
</script>