<?php if (!empty($recent_object)): ?>
    <?php
    $recent_type = !empty($recent_type) ? $recent_type : 'places';
    $object_type = !empty($object_type) ? $object_type : $recent_type;
    ?>
    <script>
        halovn.recentHistoryType = '<?php echo $recent_type ?>';
        halovn.recentHistoryObject = {
            id: "<?php echo $recent_object['id'] ?>",
            name: "<?php echo $recent_object['name'] ?>",
            address: "<?php echo!empty($recent_object['address']) ? $recent_object['address'] : '' ?>",
            type: "<?php echo $object_type ?>"
        };
    <?php if (!empty($recent_url)): ?>
            halovn.recentHistoryObject.url = "<?php echo $recent_url ?>";
    <?php else: ?>
        <?php
        $recent_url = Router::url(array(
                    'controller' => ucfirst($object_type),
                    'action' => 'info',
                    '?' => array(
                        'id' => $recent_object['id'],
                    ),
                        ), true);
        ?>
            halovn.recentHistoryObject.url = "<?php echo $recent_url ?>";
    <?php endif; ?>
        halovn.saveRecentHistory(halovn.recentHistoryType, halovn.recentHistoryObject);
    </script>
<?php endif; ?>