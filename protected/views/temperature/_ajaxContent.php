<?php if (!empty($weather)) : ?>
<?php
    /**
     * This file will be rendered in the <div id="weather"></div> tag
     * You can see that we can access the $weather variable as mentioned in TemperatureController file
     */
?>
<table>
    <tr>
        <th>Location</th>
        <th>Temp in C</th>
        <th>Pressure</th>
        <th>Humidity</th>
        <th>Timestamp</th>
    </tr>
    <tr>
        <td><?php echo $weather['location_name']; ?></td>
        <td><?php echo $weather['temp']; ?></td>
        <td><?php echo $weather['pressure']; ?></td>
        <td><?php echo $weather['humidity']; ?></td>
        <td><?php echo $weather['timestamp']; ?></td>
    </tr>
</table>
<?php endif; ?>