<?php

namespace App\Livewire\Admin\Settings;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class General extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $generalSettings = settings()->group('general')->all(false);
        $linksSettings = settings()->group('social_media_links')->all(false);

        $settings = [
            'site_name' => $generalSettings['site_name'],
            'site_language' => $generalSettings['site_language'],
            'site_logo' => $generalSettings['site_logo'],
            'site_logo_dark' => $generalSettings['site_logo_dark'],
            'site_favicon' => $generalSettings['site_favicon'],
            'facebook' => $linksSettings['facebook'],
            'twitter_x' => $linksSettings['x'],
            'instagram' => $linksSettings['instagram'],
            'tiktok' => $linksSettings['tiktok'],
            'twitch' => $linksSettings['twitch'],
            'vk' => $linksSettings['vk'],
            'discord' => $linksSettings['discord'],
            'telegram' => $linksSettings['telegram'],
            'youtube' => $linksSettings['youtube'],
            'linkedin' => $linksSettings['linkedin'],
            'timezone' => env('APP_TIMEZONE'),
            'registration' => config('alma.registration'),
            'cookie_active' => config('alma.cookie_active'),
            'posts_auto_approval' => config('alma.posts_auto_approval'),
        ];

        $this->form->fill($settings);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Website details'))
                    ->description(__('All the general settings shown here are applied on overall website.'))
                    ->icon('heroicon-o-cog')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                        ])
                            ->schema([
                                TextInput::make('site_name')
                                    ->label(__('Site name'))
                                    ->required()
                                    ->minLength(2)
                                    ->maxLength(50),

                                Grid::make([
                                    'default' => 1,
                                    'md' => 2,
                                ])
                                    ->schema([
                                        Select::make('timezone')
                                            ->label(__('Default Timezone'))
                                            ->options([
                                                'UTC' => 'UTC',
                                                'Africa/Abidjan' => 'Africa/Abidjan',
                                                'Africa/Accra' => 'Africa/Accra',
                                                'Africa/Addis_Ababa' => 'Africa/Addis_Ababa',
                                                'Africa/Algiers' => 'Africa/Algiers',
                                                'Africa/Asmara' => 'Africa/Asmara',
                                                'Africa/Bamako' => 'Africa/Bamako',
                                                'Africa/Bangui' => 'Africa/Bangui',
                                                'Africa/Banjul' => 'Africa/Banjul',
                                                'Africa/Bissau' => 'Africa/Bissau',
                                                'Africa/Blantyre' => 'Africa/Blantyre',
                                                'Africa/Brazzaville' => 'Africa/Brazzaville',
                                                'Africa/Bujumbura' => 'Africa/Bujumbura',
                                                'Africa/Cairo' => 'Africa/Cairo',
                                                'Africa/Casablanca' => 'Africa/Casablanca',
                                                'Africa/Ceuta' => 'Africa/Ceuta',
                                                'Africa/Conakry' => 'Africa/Conakry',
                                                'Africa/Dakar' => 'Africa/Dakar',
                                                'Africa/Dar_es_Salaam' => 'Africa/Dar_es_Salaam',
                                                'Africa/Djibouti' => 'Africa/Djibouti',
                                                'Africa/Douala' => 'Africa/Douala',
                                                'Africa/El_Aaiun' => 'Africa/El_Aaiun',
                                                'Africa/Freetown' => 'Africa/Freetown',
                                                'Africa/Harare' => 'Africa/Harare',
                                                'Africa/Johannesburg' => 'Africa/Johannesburg',
                                                'Africa/Juba' => 'Africa/Juba',
                                                'Africa/Kampala' => 'Africa/Kampala',
                                                'Africa/Khartoum' => 'Africa/Khartoum',
                                                'Africa/Kigali' => 'Africa/Kigali',
                                                'Africa/Kinshasa' => 'Africa/Kinshasa',
                                                'Africa/Lagos' => 'Africa/Lagos',
                                                'Africa/Libreville' => 'Africa/Libreville',
                                                'Africa/Lome' => 'Africa/Lome',
                                                'Africa/Luanda' => 'Africa/Luanda',
                                                'Africa/Lubumbashi' => 'Africa/Lubumbashi',
                                                'Africa/Lusaka' => 'Africa/Lusaka',
                                                'Africa/Malabo' => 'Africa/Malabo',
                                                'Africa/Maputo' => 'Africa/Maputo',
                                                'Africa/Maseru' => 'Africa/Maseru',
                                                'Africa/Mbabane' => 'Africa/Mbabane',
                                                'Africa/Mogadishu' => 'Africa/Mogadishu',
                                                'Africa/Monrovia' => 'Africa/Monrovia',
                                                'Africa/Nairobi' => 'Africa/Nairobi',
                                                'Africa/Ndjamena' => 'Africa/Ndjamena',
                                                'Africa/Niamey' => 'Africa/Niamey',
                                                'Africa/Nouakchott' => 'Africa/Nouakchott',
                                                'Africa/Ouagadougou' => 'Africa/Ouagadougou',
                                                'Africa/Porto-Novo' => 'Africa/Porto-Novo',
                                                'Africa/Sao_Tome' => 'Africa/Sao_Tome',
                                                'Africa/Tripoli' => 'Africa/Tripoli',
                                                'Africa/Tunis' => 'Africa/Tunis',
                                                'Africa/Windhoek' => 'Africa/Windhoek',
                                                'America/Adak' => 'America/Adak',
                                                'America/Anchorage' => 'America/Anchorage',
                                                'America/Anguilla' => 'America/Anguilla',
                                                'America/Antigua' => 'America/Antigua',
                                                'America/Araguaina' => 'America/Araguaina',
                                                'America/Argentina/Buenos_Aires' => 'America/Argentina/Buenos_Aires',
                                                'America/Argentina/Catamarca' => 'America/Argentina/Catamarca',
                                                'America/Argentina/Cordoba' => 'America/Argentina/Cordoba',
                                                'America/Argentina/Jujuy' => 'America/Argentina/Jujuy',
                                                'America/Argentina/La_Rioja' => 'America/Argentina/La_Rioja',
                                                'America/Argentina/Mendoza' => 'America/Argentina/Mendoza',
                                                'America/Argentina/Rio_Gallegos' => 'America/Argentina/Rio_Gallegos',
                                                'America/Argentina/Salta' => 'America/Argentina/Salta',
                                                'America/Argentina/San_Juan' => 'America/Argentina/San_Juan',
                                                'America/Argentina/San_Luis' => 'America/Argentina/San_Luis',
                                                'America/Argentina/Tucuman' => 'America/Argentina/Tucuman',
                                                'America/Argentina/Ushuaia' => 'America/Argentina/Ushuaia',
                                                'America/Aruba' => 'America/Aruba',
                                                'America/Asuncion' => 'America/Asuncion',
                                                'America/Atikokan' => 'America/Atikokan',
                                                'America/Bahia' => 'America/Bahia',
                                                'America/Bahia_Banderas' => 'America/Bahia_Banderas',
                                                'America/Barbados' => 'America/Barbados',
                                                'America/Belem' => 'America/Belem',
                                                'America/Belize' => 'America/Belize',
                                                'America/Blanc-Sablon' => 'America/Blanc-Sablon',
                                                'America/Boa_Vista' => 'America/Boa_Vista',
                                                'America/Bogota' => 'America/Bogota',
                                                'America/Boise' => 'America/Boise',
                                                'America/Cambridge_Bay' => 'America/Cambridge_Bay',
                                                'America/Campo_Grande' => 'America/Campo_Grande',
                                                'America/Cancun' => 'America/Cancun',
                                                'America/Caracas' => 'America/Caracas',
                                                'America/Cayenne' => 'America/Cayenne',
                                                'America/Cayman' => 'America/Cayman',
                                                'America/Chicago' => 'America/Chicago',
                                                'America/Chihuahua' => 'America/Chihuahua',
                                                'America/Costa_Rica' => 'America/Costa_Rica',
                                                'America/Creston' => 'America/Creston',
                                                'America/Cuiaba' => 'America/Cuiaba',
                                                'America/Curacao' => 'America/Curacao',
                                                'America/Danmarkshavn' => 'America/Danmarkshavn',
                                                'America/Dawson' => 'America/Dawson',
                                                'America/Dawson_Creek' => 'America/Dawson_Creek',
                                                'America/Denver' => 'America/Denver',
                                                'America/Detroit' => 'America/Detroit',
                                                'America/Dominica' => 'America/Dominica',
                                                'America/Edmonton' => 'America/Edmonton',
                                                'America/Eirunepe' => 'America/Eirunepe',
                                                'America/El_Salvador' => 'America/El_Salvador',
                                                'America/Fort_Nelson' => 'America/Fort_Nelson',
                                                'America/Fortaleza' => 'America/Fortaleza',
                                                'America/Glace_Bay' => 'America/Glace_Bay',
                                                'America/Goose_Bay' => 'America/Goose_Bay',
                                                'America/Grand_Turk' => 'America/Grand_Turk',
                                                'America/Grenada' => 'America/Grenada',
                                                'America/Guadeloupe' => 'America/Guadeloupe',
                                                'America/Guatemala' => 'America/Guatemala',
                                                'America/Guayaquil' => 'America/Guayaquil',
                                                'America/Guyana' => 'America/Guyana',
                                                'America/Halifax' => 'America/Halifax',
                                                'America/Havana' => 'America/Havana',
                                                'America/Hermosillo' => 'America/Hermosillo',
                                                'America/Indiana/Indianapolis' => 'America/Indiana/Indianapolis',
                                                'America/Indiana/Knox' => 'America/Indiana/Knox',
                                                'America/Indiana/Marengo' => 'America/Indiana/Marengo',
                                                'America/Indiana/Petersburg' => 'America/Indiana/Petersburg',
                                                'America/Indiana/Tell_City' => 'America/Indiana/Tell_City',
                                                'America/Indiana/Vevay' => 'America/Indiana/Vevay',
                                                'America/Indiana/Vincennes' => 'America/Indiana/Vincennes',
                                                'America/Indiana/Winamac' => 'America/Indiana/Winamac',
                                                'America/Inuvik' => 'America/Inuvik',
                                                'America/Iqaluit' => 'America/Iqaluit',
                                                'America/Jamaica' => 'America/Jamaica',
                                                'America/Juneau' => 'America/Juneau',
                                                'America/Kentucky/Louisville' => 'America/Kentucky/Louisville',
                                                'America/Kentucky/Monticello' => 'America/Kentucky/Monticello',
                                                'America/Kralendijk' => 'America/Kralendijk',
                                                'America/La_Paz' => 'America/La_Paz',
                                                'America/Lima' => 'America/Lima',
                                                'America/Los_Angeles' => 'America/Los_Angeles',
                                                'America/Lower_Princes' => 'America/Lower_Princes',
                                                'America/Maceio' => 'America/Maceio',
                                                'America/Managua' => 'America/Managua',
                                                'America/Manaus' => 'America/Manaus',
                                                'America/Marigot' => 'America/Marigot',
                                                'America/Martinique' => 'America/Martinique',
                                                'America/Matamoros' => 'America/Matamoros',
                                                'America/Mazatlan' => 'America/Mazatlan',
                                                'America/Menominee' => 'America/Menominee',
                                                'America/Merida' => 'America/Merida',
                                                'America/Metlakatla' => 'America/Metlakatla',
                                                'America/Mexico_City' => 'America/Mexico_City',
                                                'America/Miquelon' => 'America/Miquelon',
                                                'America/Moncton' => 'America/Moncton',
                                                'America/Monterrey' => 'America/Monterrey',
                                                'America/Montevideo' => 'America/Montevideo',
                                                'America/Montserrat' => 'America/Montserrat',
                                                'America/Nassau' => 'America/Nassau',
                                                'America/New_York' => 'America/New_York',
                                                'America/Nipigon' => 'America/Nipigon',
                                                'America/Nome' => 'America/Nome',
                                                'America/Noronha' => 'America/Noronha',
                                                'America/North_Dakota/Beulah' => 'America/North_Dakota/Beulah',
                                                'America/North_Dakota/Center' => 'America/North_Dakota/Center',
                                                'America/North_Dakota/New_Salem' => 'America/North_Dakota/New_Salem',
                                                'America/Nuuk' => 'America/Nuuk',
                                                'America/Ojinaga' => 'America/Ojinaga',
                                                'America/Panama' => 'America/Panama',
                                                'America/Pangnirtung' => 'America/Pangnirtung',
                                                'America/Paramaribo' => 'America/Paramaribo',
                                                'America/Phoenix' => 'America/Phoenix',
                                                'America/Port-au-Prince' => 'America/Port-au-Prince',
                                                'America/Port_of_Spain' => 'America/Port_of_Spain',
                                                'America/Porto_Velho' => 'America/Porto_Velho',
                                                'America/Puerto_Rico' => 'America/Puerto_Rico',
                                                'America/Punta_Arenas' => 'America/Punta_Arenas',
                                                'America/Rainy_River' => 'America/Rainy_River',
                                                'America/Rankin_Inlet' => 'America/Rankin_Inlet',
                                                'America/Recife' => 'America/Recife',
                                                'America/Regina' => 'America/Regina',
                                                'America/Resolute' => 'America/Resolute',
                                                'America/Rio_Branco' => 'America/Rio_Branco',
                                                'America/Santarem' => 'America/Santarem',
                                                'America/Santiago' => 'America/Santiago',
                                                'America/Santo_Domingo' => 'America/Santo_Domingo',
                                                'America/Sao_Paulo' => 'America/Sao_Paulo',
                                                'America/Scoresbysund' => 'America/Scoresbysund',
                                                'America/Sitka' => 'America/Sitka',
                                                'America/St_Barthelemy' => 'America/St_Barthelemy',
                                                'America/St_Johns' => 'America/St_Johns',
                                                'America/St_Kitts' => 'America/St_Kitts',
                                                'America/St_Lucia' => 'America/St_Lucia',
                                                'America/St_Thomas' => 'America/St_Thomas',
                                                'America/St_Vincent' => 'America/St_Vincent',
                                                'America/Swift_Current' => 'America/Swift_Current',
                                                'America/Tegucigalpa' => 'America/Tegucigalpa',
                                                'America/Thule' => 'America/Thule',
                                                'America/Thunder_Bay' => 'America/Thunder_Bay',
                                                'America/Tijuana' => 'America/Tijuana',
                                                'America/Toronto' => 'America/Toronto',
                                                'America/Tortola' => 'America/Tortola',
                                                'America/Vancouver' => 'America/Vancouver',
                                                'America/Whitehorse' => 'America/Whitehorse',
                                                'America/Winnipeg' => 'America/Winnipeg',
                                                'America/Yakutat' => 'America/Yakutat',
                                                'America/Yellowknife' => 'America/Yellowknife',
                                                'Antarctica/Casey' => 'Antarctica/Casey',
                                                'Antarctica/Davis' => 'Antarctica/Davis',
                                                'Antarctica/DumontDUrville' => 'Antarctica/DumontDUrville',
                                                'Antarctica/Macquarie' => 'Antarctica/Macquarie',
                                                'Antarctica/Mawson' => 'Antarctica/Mawson',
                                                'Antarctica/McMurdo' => 'Antarctica/McMurdo',
                                                'Antarctica/Palmer' => 'Antarctica/Palmer',
                                                'Antarctica/Rothera' => 'Antarctica/Rothera',
                                                'Antarctica/Syowa' => 'Antarctica/Syowa',
                                                'Antarctica/Troll' => 'Antarctica/Troll',
                                                'Antarctica/Vostok' => 'Antarctica/Vostok',
                                                'Arctic/Longyearbyen' => 'Arctic/Longyearbyen',
                                                'Asia/Aden' => 'Asia/Aden',
                                                'Asia/Almaty' => 'Asia/Almaty',
                                                'Asia/Amman' => 'Asia/Amman',
                                                'Asia/Anadyr' => 'Asia/Anadyr',
                                                'Asia/Aqtau' => 'Asia/Aqtau',
                                                'Asia/Aqtobe' => 'Asia/Aqtobe',
                                                'Asia/Ashgabat' => 'Asia/Ashgabat',
                                                'Asia/Atyrau' => 'Asia/Atyrau',
                                                'Asia/Baghdad' => 'Asia/Baghdad',
                                                'Asia/Bahrain' => 'Asia/Bahrain',
                                                'Asia/Baku' => 'Asia/Baku',
                                                'Asia/Bangkok' => 'Asia/Bangkok',
                                                'Asia/Barnaul' => 'Asia/Barnaul',
                                                'Asia/Beirut' => 'Asia/Beirut',
                                                'Asia/Bishkek' => 'Asia/Bishkek',
                                                'Asia/Brunei' => 'Asia/Brunei',
                                                'Asia/Chita' => 'Asia/Chita',
                                                'Asia/Choibalsan' => 'Asia/Choibalsan',
                                                'Asia/Colombo' => 'Asia/Colombo',
                                                'Asia/Damascus' => 'Asia/Damascus',
                                                'Asia/Dhaka' => 'Asia/Dhaka',
                                                'Asia/Dili' => 'Asia/Dili',
                                                'Asia/Dubai' => 'Asia/Dubai',
                                                'Asia/Dushanbe' => 'Asia/Dushanbe',
                                                'Asia/Famagusta' => 'Asia/Famagusta',
                                                'Asia/Gaza' => 'Asia/Gaza',
                                                'Asia/Hebron' => 'Asia/Hebron',
                                                'Asia/Ho_Chi_Minh' => 'Asia/Ho_Chi_Minh',
                                                'Asia/Hong_Kong' => 'Asia/Hong_Kong',
                                                'Asia/Hovd' => 'Asia/Hovd',
                                                'Asia/Irkutsk' => 'Asia/Irkutsk',
                                                'Asia/Jakarta' => 'Asia/Jakarta',
                                                'Asia/Jayapura' => 'Asia/Jayapura',
                                                'Asia/Jerusalem' => 'Asia/Jerusalem',
                                                'Asia/Kabul' => 'Asia/Kabul',
                                                'Asia/Kamchatka' => 'Asia/Kamchatka',
                                                'Asia/Karachi' => 'Asia/Karachi',
                                                'Asia/Kathmandu' => 'Asia/Kathmandu',
                                                'Asia/Khandyga' => 'Asia/Khandyga',
                                                'Asia/Kolkata' => 'Asia/Kolkata',
                                                'Asia/Krasnoyarsk' => 'Asia/Krasnoyarsk',
                                                'Asia/Kuala_Lumpur' => 'Asia/Kuala_Lumpur',
                                                'Asia/Kuching' => 'Asia/Kuching',
                                                'Asia/Kuwait' => 'Asia/Kuwait',
                                                'Asia/Macau' => 'Asia/Macau',
                                                'Asia/Magadan' => 'Asia/Magadan',
                                                'Asia/Makassar' => 'Asia/Makassar',
                                                'Asia/Manila' => 'Asia/Manila',
                                                'Asia/Muscat' => 'Asia/Muscat',
                                                'Asia/Nicosia' => 'Asia/Nicosia',
                                                'Asia/Novokuznetsk' => 'Asia/Novokuznetsk',
                                                'Asia/Novosibirsk' => 'Asia/Novosibirsk',
                                                'Asia/Omsk' => 'Asia/Omsk',
                                                'Asia/Oral' => 'Asia/Oral',
                                                'Asia/Phnom_Penh' => 'Asia/Phnom_Penh',
                                                'Asia/Pontianak' => 'Asia/Pontianak',
                                                'Asia/Pyongyang' => 'Asia/Pyongyang',
                                                'Asia/Qatar' => 'Asia/Qatar',
                                                'Asia/Qostanay' => 'Asia/Qostanay',
                                                'Asia/Qyzylorda' => 'Asia/Qyzylorda',
                                                'Asia/Riyadh' => 'Asia/Riyadh',
                                                'Asia/Sakhalin' => 'Asia/Sakhalin',
                                                'Asia/Samarkand' => 'Asia/Samarkand',
                                                'Asia/Seoul' => 'Asia/Seoul',
                                                'Asia/Shanghai' => 'Asia/Shanghai',
                                                'Asia/Singapore' => 'Asia/Singapore',
                                                'Asia/Srednekolymsk' => 'Asia/Srednekolymsk',
                                                'Asia/Taipei' => 'Asia/Taipei',
                                                'Asia/Tashkent' => 'Asia/Tashkent',
                                                'Asia/Tbilisi' => 'Asia/Tbilisi',
                                                'Asia/Tehran' => 'Asia/Tehran',
                                                'Asia/Thimphu' => 'Asia/Thimphu',
                                                'Asia/Tokyo' => 'Asia/Tokyo',
                                                'Asia/Tomsk' => 'Asia/Tomsk',
                                                'Asia/Ulaanbaatar' => 'Asia/Ulaanbaatar',
                                                'Asia/Urumqi' => 'Asia/Urumqi',
                                                'Asia/Ust-Nera' => 'Asia/Ust-Nera',
                                                'Asia/Vientiane' => 'Asia/Vientiane',
                                                'Asia/Vladivostok' => 'Asia/Vladivostok',
                                                'Asia/Yakutsk' => 'Asia/Yakutsk',
                                                'Asia/Yangon' => 'Asia/Yangon',
                                                'Asia/Yekaterinburg' => 'Asia/Yekaterinburg',
                                                'Asia/Yerevan' => 'Asia/Yerevan',
                                                'Atlantic/Azores' => 'Atlantic/Azores',
                                                'Atlantic/Bermuda' => 'Atlantic/Bermuda',
                                                'Atlantic/Canary' => 'Atlantic/Canary',
                                                'Atlantic/Cape_Verde' => 'Atlantic/Cape_Verde',
                                                'Atlantic/Faroe' => 'Atlantic/Faroe',
                                                'Atlantic/Madeira' => 'Atlantic/Madeira',
                                                'Atlantic/Reykjavik' => 'Atlantic/Reykjavik',
                                                'Atlantic/South_Georgia' => 'Atlantic/South_Georgia',
                                                'Atlantic/St_Helena' => 'Atlantic/St_Helena',
                                                'Atlantic/Stanley' => 'Atlantic/Stanley',
                                                'Australia/Adelaide' => 'Australia/Adelaide',
                                                'Australia/Brisbane' => 'Australia/Brisbane',
                                                'Australia/Broken_Hill' => 'Australia/Broken_Hill',
                                                'Australia/Darwin' => 'Australia/Darwin',
                                                'Australia/Eucla' => 'Australia/Eucla',
                                                'Australia/Hobart' => 'Australia/Hobart',
                                                'Australia/Lindeman' => 'Australia/Lindeman',
                                                'Australia/Lord_Howe' => 'Australia/Lord_Howe',
                                                'Australia/Melbourne' => 'Australia/Melbourne',
                                                'Australia/Perth' => 'Australia/Perth',
                                                'Australia/Sydney' => 'Australia/Sydney',
                                                'Europe/Amsterdam' => 'Europe/Amsterdam',
                                                'Europe/Andorra' => 'Europe/Andorra',
                                                'Europe/Astrakhan' => 'Europe/Astrakhan',
                                                'Europe/Athens' => 'Europe/Athens',
                                                'Europe/Belgrade' => 'Europe/Belgrade',
                                                'Europe/Berlin' => 'Europe/Berlin',
                                                'Europe/Bratislava' => 'Europe/Bratislava',
                                                'Europe/Brussels' => 'Europe/Brussels',
                                                'Europe/Bucharest' => 'Europe/Bucharest',
                                                'Europe/Budapest' => 'Europe/Budapest',
                                                'Europe/Busingen' => 'Europe/Busingen',
                                                'Europe/Chisinau' => 'Europe/Chisinau',
                                                'Europe/Copenhagen' => 'Europe/Copenhagen',
                                                'Europe/Dublin' => 'Europe/Dublin',
                                                'Europe/Gibraltar' => 'Europe/Gibraltar',
                                                'Europe/Guernsey' => 'Europe/Guernsey',
                                                'Europe/Helsinki' => 'Europe/Helsinki',
                                                'Europe/Isle_of_Man' => 'Europe/Isle_of_Man',
                                                'Europe/Istanbul' => 'Europe/Istanbul',
                                                'Europe/Jersey' => 'Europe/Jersey',
                                                'Europe/Kaliningrad' => 'Europe/Kaliningrad',
                                                'Europe/Kirov' => 'Europe/Kirov',
                                                'Europe/Kyiv' => 'Europe/Kyiv',
                                                'Europe/Lisbon' => 'Europe/Lisbon',
                                                'Europe/Ljubljana' => 'Europe/Ljubljana',
                                                'Europe/London' => 'Europe/London',
                                                'Europe/Luxembourg' => 'Europe/Luxembourg',
                                                'Europe/Madrid' => 'Europe/Madrid',
                                                'Europe/Malta' => 'Europe/Malta',
                                                'Europe/Mariehamn' => 'Europe/Mariehamn',
                                                'Europe/Minsk' => 'Europe/Minsk',
                                                'Europe/Monaco' => 'Europe/Monaco',
                                                'Europe/Moscow' => 'Europe/Moscow',
                                                'Europe/Oslo' => 'Europe/Oslo',
                                                'Europe/Paris' => 'Europe/Paris',
                                                'Europe/Podgorica' => 'Europe/Podgorica',
                                                'Europe/Prague' => 'Europe/Prague',
                                                'Europe/Riga' => 'Europe/Riga',
                                                'Europe/Rome' => 'Europe/Rome',
                                                'Europe/Samara' => 'Europe/Samara',
                                                'Europe/San_Marino' => 'Europe/San_Marino',
                                                'Europe/Sarajevo' => 'Europe/Sarajevo',
                                                'Europe/Saratov' => 'Europe/Saratov',
                                                'Europe/Simferopol' => 'Europe/Simferopol',
                                                'Europe/Skopje' => 'Europe/Skopje',
                                                'Europe/Sofia' => 'Europe/Sofia',
                                                'Europe/Stockholm' => 'Europe/Stockholm',
                                                'Europe/Tallinn' => 'Europe/Tallinn',
                                                'Europe/Tirane' => 'Europe/Tirane',
                                                'Europe/Ulyanovsk' => 'Europe/Ulyanovsk',
                                                'Europe/Uzhgorod' => 'Europe/Uzhgorod',
                                                'Europe/Vaduz' => 'Europe/Vaduz',
                                                'Europe/Vatican' => 'Europe/Vatican',
                                                'Europe/Vienna' => 'Europe/Vienna',
                                                'Europe/Vilnius' => 'Europe/Vilnius',
                                                'Europe/Volgograd' => 'Europe/Volgograd',
                                                'Europe/Warsaw' => 'Europe/Warsaw',
                                                'Europe/Zagreb' => 'Europe/Zagreb',
                                                'Europe/Zaporozhye' => 'Europe/Zaporozhye',
                                                'Europe/Zurich' => 'Europe/Zurich',
                                                'Indian/Antananarivo' => 'Indian/Antananarivo',
                                                'Indian/Chagos' => 'Indian/Chagos',
                                                'Indian/Christmas' => 'Indian/Christmas',
                                                'Indian/Cocos' => 'Indian/Cocos',
                                                'Indian/Comoro' => 'Indian/Comoro',
                                                'Indian/Kerguelen' => 'Indian/Kerguelen',
                                                'Indian/Mahe' => 'Indian/Mahe',
                                                'Indian/Maldives' => 'Indian/Maldives',
                                                'Indian/Mauritius' => 'Indian/Mauritius',
                                                'Indian/Mayotte' => 'Indian/Mayotte',
                                                'Indian/Reunion' => 'Indian/Reunion',
                                                'Pacific/Apia' => 'Pacific/Apia',
                                                'Pacific/Auckland' => 'Pacific/Auckland',
                                                'Pacific/Bougainville' => 'Pacific/Bougainville',
                                                'Pacific/Chatham' => 'Pacific/Chatham',
                                                'Pacific/Chuuk' => 'Pacific/Chuuk',
                                                'Pacific/Easter' => 'Pacific/Easter',
                                                'Pacific/Efate' => 'Pacific/Efate',
                                                'Pacific/Fakaofo' => 'Pacific/Fakaofo',
                                                'Pacific/Fiji' => 'Pacific/Fiji',
                                                'Pacific/Funafuti' => 'Pacific/Funafuti',
                                                'Pacific/Galapagos' => 'Pacific/Galapagos',
                                                'Pacific/Gambier' => 'Pacific/Gambier',
                                                'Pacific/Guadalcanal' => 'Pacific/Guadalcanal',
                                                'Pacific/Guam' => 'Pacific/Guam',
                                                'Pacific/Honolulu' => 'Pacific/Honolulu',
                                                'Pacific/Kanton' => 'Pacific/Kanton',
                                                'Pacific/Kiritimati' => 'Pacific/Kiritimati',
                                                'Pacific/Kosrae' => 'Pacific/Kosrae',
                                                'Pacific/Kwajalein' => 'Pacific/Kwajalein',
                                                'Pacific/Majuro' => 'Pacific/Majuro',
                                                'Pacific/Marquesas' => 'Pacific/Marquesas',
                                                'Pacific/Midway' => 'Pacific/Midway',
                                                'Pacific/Nauru' => 'Pacific/Nauru',
                                                'Pacific/Niue' => 'Pacific/Niue',
                                                'Pacific/Norfolk' => 'Pacific/Norfolk',
                                                'Pacific/Noumea' => 'Pacific/Noumea',
                                                'Pacific/Pago_Pago' => 'Pacific/Pago_Pago',
                                                'Pacific/Palau' => 'Pacific/Palau',
                                                'Pacific/Pitcairn' => 'Pacific/Pitcairn',
                                                'Pacific/Pohnpei' => 'Pacific/Pohnpei',
                                                'Pacific/Port_Moresby' => 'Pacific/Port_Moresby',
                                                'Pacific/Rarotonga' => 'Pacific/Rarotonga',
                                                'Pacific/Saipan' => 'Pacific/Saipan',
                                                'Pacific/Tahiti' => 'Pacific/Tahiti',
                                                'Pacific/Tarawa' => 'Pacific/Tarawa',
                                                'Pacific/Tongatapu' => 'Pacific/Tongatapu',
                                                'Pacific/Wake' => 'Pacific/Wake',
                                                'Pacific/Wallis' => 'Pacific/Wallis',
                                            ])
                                            ->default('UTC')
                                            ->selectablePlaceholder(false)
                                            ->native(false)
                                            ->searchable(),
                                        Select::make('site_language')
                                            ->label(__('Default Language'))
                                            ->options([
                                                'en' => 'English',
                                                'de' => 'German',
                                                'fr' => 'French',
                                                'ru' => 'Russian',
                                                'it' => 'Italian',
                                                'pt' => 'Portuguese',
                                                'es' => 'Spanish',
                                                'tr' => 'Turkish',
                                                'sk' => 'Slovak',
                                                'hu' => 'Hungarian',
                                                'id' => 'Indonesian',
                                                'vi' => 'Vietnamese',
                                                'uk' => 'Ukrainian',
                                            ])
                                            ->default('en')
                                            ->selectablePlaceholder(false)
                                            ->native(false),
                                    ]),
                            ]),

                    ])->collapsible(),
                Section::make(__('Actions'))
                    ->icon('heroicon-o-cog')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                        ])
                            ->schema([
                                Toggle::make('registration')
                                    ->label(__('Registration'))
                                    ->onIcon('heroicon-m-bolt')
                                    ->offIcon('heroicon-m-bolt-slash')
                                    ->disabled(env('DEMO_MODE') == true)
                                    ->live(onBlur: true),
                                Toggle::make('cookie_active')
                                    ->label(__('Cookie consent'))
                                    ->onIcon('heroicon-m-bolt')
                                    ->offIcon('heroicon-m-bolt-slash')
                                    ->live(onBlur: true),
                                Toggle::make('posts_auto_approval')
                                    ->label(__('Auto-approval of posts'))
                                    ->onIcon('heroicon-m-bolt')
                                    ->offIcon('heroicon-m-bolt-slash')
                                    ->live(onBlur: true),
                            ]),
                    ])->collapsible(),
                Section::make(__('Social network profiles'))
                    ->description(__('These links will be shown in the footer menu.'))
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                        ])
                            ->schema([
                                TextInput::make('facebook')
                                    ->label(__('Facebook'))
                                    ->placeholder(__('https://www.facebook.com/{profile-link}')),
                                TextInput::make('twitter_x')
                                    ->label(__('X (Twitter)'))
                                    ->placeholder(__('https://www.twitter.com/{profile-link}')),
                                TextInput::make('instagram')
                                    ->label(__('Instagram'))
                                    ->placeholder(__('https://www.instagram.com/{profile-link}')),
                                TextInput::make('tiktok')
                                    ->label(__('Tiktok'))
                                    ->placeholder(__('https://www.tiktok.com/{profile-link}')),
                                TextInput::make('twitch')
                                    ->label(__('Twitch'))
                                    ->placeholder(__('https://www.twitch.tv/{profile-link}')),
                                TextInput::make('vk')
                                    ->label(__('VK'))
                                    ->placeholder(__('https://www.vk.com/{profile-link}')),
                                TextInput::make('discord')
                                    ->label(__('Discord'))
                                    ->placeholder(__('https://discord.gg/{id}')),
                                TextInput::make('telegram')
                                    ->label(__('Telegram'))
                                    ->placeholder(__('https://www.t.me/{username}')),
                                TextInput::make('youtube')
                                    ->label(__('Youtube'))
                                    ->placeholder(__('https://www.youtube.com/{username}')),
                                TextInput::make('linkedin')
                                    ->label(__('LinkedIn'))
                                    ->placeholder(__('https://www.linkedin.com/{profile-link}')),
                            ]),
                    ])->collapsible()->collapsed(),
                Section::make(__('Assets'))
                    ->description(__('Upload logo, dark logo and favicon of your platform, after upload will be visible on your site.'))
                    ->icon('heroicon-o-photo')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 3,
                        ])
                            ->schema([
                                FileUpload::make('site_logo')
                                    ->label(__('Logo'))
                                    ->placeholder(__('Upload your site logo from here. (Only PNG)'))
                                    ->image()
                                    ->acceptedFileTypes(['image/png'])
                                    ->disk(getCurrentDisk())
                                    ->directory('media')
                                    ->visibility('public')
                                    ->maxSize(1024)
                                    ->afterStateUpdated(fn () => $this->validateOnly('data.site_logo')),
                                FileUpload::make('site_logo_dark')
                                    ->label(__('Dark Logo'))
                                    ->placeholder(__('Upload your site dark logo from here. (Only PNG)'))
                                    ->image()
                                    ->acceptedFileTypes(['image/png'])
                                    ->disk(getCurrentDisk())
                                    ->directory('media')
                                    ->visibility('public')
                                    ->maxSize(1024)
                                    ->afterStateUpdated(fn () => $this->validateOnly('data.site_logo_dark')),
                                FileUpload::make('site_favicon')
                                    ->label(__('Favicon'))
                                    ->placeholder(__('Upload a favicon here. (Only PNG)'))
                                    ->image()
                                    ->acceptedFileTypes(['image/png'])
                                    ->disk(getCurrentDisk())
                                    ->directory('media')
                                    ->visibility('public')
                                    ->maxSize(1024)
                                    ->afterStateUpdated(fn () => $this->validateOnly('data.site_favicon')),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function store()
    {
        if (env('DEMO_MODE')) {
            Notification::make()
                ->warning()
                ->title('Opps! You are in demo mode')
                ->seconds(10)
                ->send();

            return;
        }

        if (! is_null($this->form->getState()['site_name'])) {
            settings()->group('general')->set('site_name', $this->form->getState()['site_name']);
            setEnv('APP_NAME', $this->form->getState()['site_name']);
        } else {
            settings()->group('general')->set('site_name', '');
            setEnv('APP_NAME', '');
        }

        if ($this->form->getState()['site_language']) {
            settings()->group('general')->set('site_language', $this->form->getState()['site_language']);
        }

        if ($this->form->getState()['timezone']) {
            setEnv('APP_TIMEZONE', $this->form->getState()['timezone']);
        }

        if ($this->form->getState()['registration']) {
            Config::write('alma.registration', $this->form->getState()['registration']);
        } else {
            Config::write('alma.registration', $this->form->getState()['registration']);
        }

        if ($this->form->getState()['cookie_active']) {
            Config::write('alma.cookie_active', $this->form->getState()['cookie_active']);
        } else {
            Config::write('alma.cookie_active', $this->form->getState()['cookie_active']);
        }

        if ($this->form->getState()['posts_auto_approval']) {
            Config::write('alma.posts_auto_approval', $this->form->getState()['posts_auto_approval']);
        } else {
            Config::write('alma.posts_auto_approval', $this->form->getState()['posts_auto_approval']);
        }

        if (! is_null($this->form->getState()['facebook'])) {
            settings()->group('social_media_links')->set('facebook', $this->form->getState()['facebook']);
        } else {
            settings()->group('social_media_links')->set('facebook', '');
        }

        if (! is_null($this->form->getState()['twitter_x'])) {
            settings()->group('social_media_links')->set('x', $this->form->getState()['twitter_x']);
        } else {
            settings()->group('social_media_links')->set('x', '');
        }

        if (! is_null($this->form->getState()['instagram'])) {
            settings()->group('social_media_links')->set('instagram', $this->form->getState()['instagram']);
        } else {
            settings()->group('social_media_links')->set('instagram', '');
        }

        if (! is_null($this->form->getState()['tiktok'])) {
            settings()->group('social_media_links')->set('tiktok', $this->form->getState()['tiktok']);
        } else {
            settings()->group('social_media_links')->set('tiktok', '');
        }

        if (! is_null($this->form->getState()['twitch'])) {
            settings()->group('social_media_links')->set('twitch', $this->form->getState()['twitch']);
        } else {
            settings()->group('social_media_links')->set('twitch', '');
        }

        if (! is_null($this->form->getState()['vk'])) {
            settings()->group('social_media_links')->set('vk', $this->form->getState()['vk']);
        } else {
            settings()->group('social_media_links')->set('vk', '');
        }

        if (! is_null($this->form->getState()['discord'])) {
            settings()->group('social_media_links')->set('discord', $this->form->getState()['discord']);
        } else {
            settings()->group('social_media_links')->set('discord', '');
        }

        if (! is_null($this->form->getState()['telegram'])) {
            settings()->group('social_media_links')->set('telegram', $this->form->getState()['telegram']);
        } else {
            settings()->group('social_media_links')->set('telegram', '');
        }

        if (! is_null($this->form->getState()['youtube'])) {
            settings()->group('social_media_links')->set('youtube', $this->form->getState()['youtube']);
        } else {
            settings()->group('social_media_links')->set('youtube', '');
        }

        if (! is_null($this->form->getState()['linkedin'])) {
            settings()->group('social_media_links')->set('linkedin', $this->form->getState()['linkedin']);
        } else {
            settings()->group('social_media_links')->set('linkedin', '');
        }

        if (! is_null($this->form->getState()['site_logo'])) {
            settings()->group('general')->set('site_logo', $this->form->getState()['site_logo']);
        } else {
            $site_logo = settings()->group('general')->get('site_logo');
            if (isset($site_logo) && Storage::disk(getCurrentDisk())->exists($site_logo)) {
                Storage::disk(getCurrentDisk())->delete($site_logo);
                settings()->group('general')->set('site_logo', '');
            }
        }

        if (! is_null($this->form->getState()['site_logo_dark'])) {
            settings()->group('general')->set('site_logo_dark', $this->form->getState()['site_logo_dark']);
        } else {
            $site_logo_dark = settings()->group('general')->get('site_logo_dark');
            if (isset($site_logo_dark) && Storage::disk(getCurrentDisk())->exists($site_logo_dark)) {
                Storage::disk(getCurrentDisk())->delete($site_logo_dark);
                settings()->group('general')->set('site_logo_dark', '');
            }
        }

        if (! is_null($this->form->getState()['site_favicon'])) {
            settings()->group('general')->set('site_favicon', $this->form->getState()['site_favicon']);
        } else {
            $site_favicon = settings()->group('general')->get('site_favicon');
            if (isset($site_favicon) && Storage::disk(getCurrentDisk())->exists($site_favicon)) {
                Storage::disk(getCurrentDisk())->delete($site_favicon);
                settings()->group('general')->set('site_favicon', '');
            }
        }

        // Clear all cache
        Artisan::call('optimize:clear');

        Notification::make()
            ->success()
            ->title(__('Settings successfully updated'))
            ->seconds(10)
            ->send();
    }

    public function render()
    {
        return view('livewire.admin.settings.general');
    }
}
