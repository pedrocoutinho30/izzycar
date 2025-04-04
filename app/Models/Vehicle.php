<?php

// app/Models/Vehicle.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'brand',
        'model',
        'version',
        'year',
        'kilometers',
        'power',
        'cylinder_capacity',
        'consigned_vehicle',
        'fuel',
        'purchase_type',
        'purchase_date',
        'purchase_price',
        'sell_price',
        'sold',
        'sale_date',
        'business_type',
        'vin',
        'manufacture_date',
        'register_date',
        'available_to_sell_date',
        'registration',
        'segment',
        'color',
        'locality',
        'observation',
        'guarantee',
        'guarantee_kms',
        'guarantee_brand',
        'inspection_until',
        'number_registrations',
        'origin',
        'complete_review_book',
        'second_key',
        'non_smoker',
        'supplier_id',
        'box_type',
        'changes_number',
        'door_number',
        'capacity',
        'traction',
        'autonomy',
        'co2',
        'iuc',
        'roof_racks',
        'tow_hook',
        'light_wheels',
        'size_wheels',
        'fog_lights',
        'directional_lights',
        'daytime_running_lights',
        'height_adjustable_lights',
        'xenon_head_lights',
        'bixenon_head_lights',
        'headlights_with_brightness_sensor',
        'led_front_headlights',
        'led_rear_lights',
        'full_led_headlights',
        'heated_mirrors',
        'electric_adjustable_mirrors',
        'manual_adjustable_mirrors',
        'auto_dimming_mirrors',
        'power_folding_mirrors',
        'front_power_windows',
        'rear_power_windows',
        'wood_steering_wheel',
        'leather_steering_wheel',
        'multifunction_steering_wheel',
        'heated_multifunction_steering_wheel',
        'steering_wheel_audio_controls',
        'height_adjustable_steering_wheel',
        'depth_adjustable_steering_wheel',
        'electric_adjustable_steering_wheel',
        'sunroof',
        'fabric_seats',
        'leather_seats',
        'alcantara_seats',
        'heated_seats',
        'ventilated_seats',
        'massage_seats',
        'lumbar_support_seats',
        'interior_color',
        'armrest',
        'heated_front_seats',
        'front_seats_with_memory',
        'electric_adjustable_front_seats',
        'heated_rear_seats',
        'individual_adjustable_rear_seats',
        'rear_door_sunshades',
        'rear_headrests',
        'sport_seats',
        'side_airbags',
        'driver_airbag',
        'passenger_airbag',
        'safety_performance',
        'alarm',
        'isofix',
        'immobilizer',
        'electric_abs',
        'power_steering',
        'night_driving_assistance',
        'electronic_differential_lock_eds',
        'electronic_stability_control_esp',
        'automatic_parking',
        'automatic_door_lock_while_driving',
        'torque_regulator_msr',
        'hill_start_assist',
        'individual_smart_key',
        'tire_pressure_monitoring',
        'sport_suspension',
        'air_suspension',
        'sport_package',
        'start_stop_system',
        'rain_sensors',
        'parking_sensors',
        'rearview_camera',
        'central_locking_remote',
        'mechanical_tuning',
        'air_conditioning',
        'usb_input',
        'onboard_computer',
        'cruise_control',
        'hands_free_kit',
        'automatic_tailgate',
        'roof_screen',
        'headrest_screen',
        'center_console_screen',
        'bluetooth',
        'navigation_system_gps',
        'usb_c',
        'aux_input',
        'sound_system',
        'touchscreen',
        'apple_carplay',
        'keyless_entry',
        'voice_control',
        'rear_seat_usb',
        'easy_open_close',
        'virtual_cockpit',
        'wireless_charging',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
