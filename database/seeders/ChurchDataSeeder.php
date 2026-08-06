<?php

namespace Database\Seeders;

use App\Models\Church;
use App\Models\GroupChurch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChurchDataSeeder extends Seeder
{
    /**
     * Source data transcribed from the group/church/pastor list.
     *
     * Notes on how the raw table was interpreted:
     *  - Every "... GROUP" / "... SUB GROUP" / "... SUB-GROUP" heading
     *    becomes its own GroupChurch row (flat, single-level — the app's
     *    schema doesn't nest sub-groups under groups).
     *  - Rows like "GROUP PASTOR" / "EPUTU PASTOR" with no "CHRIST
     *    EMBASSY ..." church name are the *group's* pastor, not a church,
     *    so they're intentionally left out of the church list below.
     *  - Two rows were both named "CHRIST EMBASSY DESSA CHURCH" under
     *    KAJOLA GROUP with two different pastors — the second is stored
     *    as "CHRIST EMBASSY DESSA CHURCH II" so neither entry is lost to
     *    a duplicate-name collision.
     *  - A couple of rows had no pastor listed in the source (e.g. Owode
     *    Church 2) — pastor is left null for those.
     */
    private const GROUPS = [
        'Lekki Group' => [
            ['name' => 'Christ Embassy Lekki Church', 'pastor' => 'Pastor Deola Phillips'],
        ],
        'Lekki Phase 1 Sub Group' => [
            ['name' => 'Christ Embassy The Love Center Church, Admiralty, Lekki', 'pastor' => 'Pastor Seye Dosunmu'],
            ['name' => 'Christ Embassy Lekki Phase 1 Bisola Durosinmi Etti Church', 'pastor' => 'Pastor Lauretta Asemota'],
            ['name' => 'Christ Embassy Spring Church', 'pastor' => 'Pastor Samuel Okwori'],
            ['name' => 'Christ Embassy Seaview Lekki Phase 1', 'pastor' => 'Pastor John Soji'],
        ],
        'Obalende Group' => [
            // Group pastor: Pastor Ngozi Ejiogu — no churches listed.
        ],
        'Lekki Free Trade Zone Group' => [
            ['name' => 'Christ Embassy Lekki Town Church', 'pastor' => 'Pastor Ike Nnoli'],
            ['name' => 'Christ Embassy Idiroko', 'pastor' => 'Brother Ammiel Gabeni'],
            ['name' => 'Christ Embassy Tiye', 'pastor' => 'Brother Paul Sunday'],
        ],
        'Eleko Sub Group' => [
            ['name' => 'Christ Embassy Limitless Church Orimedu', 'pastor' => 'Pastor Doris Adjedje'],
            ['name' => 'Christ Embassy Eleko', 'pastor' => 'Deacon Stephen Adjedje'],
        ],
        'Chevron Group' => [
            ['name' => 'Christ Embassy Chevron Church', 'pastor' => 'Pastor Chris Ibhakhomu'],
            ['name' => 'Christ Embassy Jakande Shoprite Environs', 'pastor' => 'Brother Anthony Akabudike'],
            ['name' => 'Christ Embassy Chevron Church 2 Ikota', 'pastor' => 'Pastor Toju Omatsola'],
            ['name' => 'Christ Embassy Chevron (New Road)', 'pastor' => 'Deacon Bukky Olusanya'],
            ['name' => 'Christ Embassy Agungi', 'pastor' => 'Brother Mccann Ifikivben'],
            ['name' => 'Christ Embassy Orchid Road Church', 'pastor' => 'Pastor Gladys Akhidime'],
            ['name' => "Christ Embassy King's Wealth Church", 'pastor' => 'Sister Progress Christian'],
        ],
        'Ikoyi Sub-Group 1' => [
            ['name' => 'Christ Embassy Ikoyi', 'pastor' => 'Pastor Obehi Eremiokhale'],
            ['name' => 'Christ Embassy Glover Road', 'pastor' => 'Pastor Obasi Nwamadi'],
            ['name' => 'Christ Embassy Awolowo', 'pastor' => 'Brother Femi Felix'],
        ],
        'Ikoyi Sub-Group 2' => [
            ['name' => 'Christ Embassy Awolowo Road Ikoyi 2', 'pastor' => 'Pastor Oghenero Deniran'],
            ['name' => 'Christ Embassy Dolphin', 'pastor' => 'Pastor Nkechi Nwosu'],
            ['name' => 'Christ Embassy Ever Increasing Church', 'pastor' => 'Sister Osas Cathy'],
            ['name' => 'Christ Embassy Oshodi', 'pastor' => 'Brother Harold Okafor'],
        ],
        'Lagos Island Group' => [
            ['name' => 'Christ Embassy Lagos Island 1', 'pastor' => 'Pastor Enahoro Zedomi'],
            ['name' => 'Christ Embassy Tinubu Square', 'pastor' => 'Pastor Samson Olanrewaju'],
            ['name' => 'Christ Embassy Gambari Church', 'pastor' => 'Brother Kwame Attipoe'],
            ['name' => 'Christ Embassy Sura Church', 'pastor' => 'Brother Stephen Akor'],
            ['name' => 'Christ Embassy Adeniji Adele', 'pastor' => 'Pastor Lanre Adio'],
            ['name' => 'Christ Embassy Pelewura', 'pastor' => 'Brother Alloysius Ebia'],
        ],
        'Victoria Island Group' => [
            ['name' => 'Christ Embassy TY Danjuma, VI Church', 'pastor' => 'Pastor Funke Oke'],
            ['name' => 'Christ Embassy Oju Olobon Close, VI', 'pastor' => 'Sister Christine Orji'],
            ['name' => 'Christ Embassy Oniru Market Road, VI', 'pastor' => 'Deaconess Busola Akindele'],
            ['name' => 'Christ Embassy Saka Tinubu', 'pastor' => 'Brother Ikechukwu Mba'],
            ['name' => 'Christ Embassy Adetokunbo Ademola', 'pastor' => 'Brother Maduforo Erondu'],
            ['name' => 'Christ Embassy Tiamiyu Salvage', 'pastor' => 'Brother Chuks Ewerebor'],
        ],
        'Victoria Island Sub-Group' => [
            ['name' => 'Christ Embassy Sagbokoji Island Church', 'pastor' => 'Pastor Victor Wealth'],
            ['name' => 'Christ Embassy Tomaro Island', 'pastor' => 'Pastor Victor Wealth'],
            ['name' => 'Christ Embassy Snake Island', 'pastor' => 'Pastor Victor Wealth'],
            ['name' => 'Christ Embassy Agala 1', 'pastor' => 'Pastor Victor Wealth'],
            ['name' => 'Christ Embassy Agala 2', 'pastor' => 'Pastor Victor Wealth'],
            ['name' => 'Christ Embassy Manager', 'pastor' => 'Pastor Victor Wealth'],
            ['name' => 'Christ Embassy Tarkwa Bay Church', 'pastor' => 'Sister Mary Ben Campos'],
            ['name' => 'Christ Embassy Ogogoro Island', 'pastor' => 'Sister Mary Ben Campos'],
            ['name' => 'Christ Embassy Itunagan Island', 'pastor' => 'Pastor Victor Wealth'],
        ],
        'Ajah Group' => [
            ['name' => 'Christ Embassy Ajah', 'pastor' => 'Pastor Don Okhuofu'],
            ['name' => 'Christ Embassy Ajah Sunrise Church', 'pastor' => 'Brother Emeka Onuoha'],
            ['name' => 'Christ Embassy Ajah Children Outreach', 'pastor' => 'Deaconess Susan Adegbohungbe'],
            ['name' => 'Christ Embassy Addo Road', 'pastor' => 'Pastor Ifeoma Madumere'],
            ['name' => 'Christ Embassy Ajah Yoruba Language Church', 'pastor' => 'Brother Dele Moyegun'],
            ['name' => 'Christ Embassy Ajah Yoruba Language Church Egun', 'pastor' => 'Brother Dele Moyegun'],
            ['name' => 'Christ Embassy Okeiranla', 'pastor' => 'Sister Josephine Irivbi-Jesu'],
        ],
        'Owode-Badore Group' => [
            ['name' => 'Christ Embassy Owode Church 1', 'pastor' => 'Pastor Noble Uchechukwu'],
            ['name' => 'Christ Embassy Owode Church 2', 'pastor' => null],
            ['name' => 'Christ Embassy Badore Waterside Church', 'pastor' => 'Brother Samuel Thomas'],
            ['name' => 'Christ Embassy Langbasa', 'pastor' => 'Pastor Dotun Oyebamiji'],
            ['name' => 'Christ Embassy Badore', 'pastor' => 'Pastor Adeyemi Adesina'],
            ['name' => 'Christ Embassy Seaside', 'pastor' => 'Brother Ikechukwu Johnson'],
        ],
        'Ajiwe Group' => [
            ['name' => 'Christ Embassy Ajiwe', 'pastor' => 'Pastor Nnenna Onyema'],
            ['name' => 'Christ Embassy Alaguntan', 'pastor' => 'Brother James Eziashi'],
            ['name' => 'Christ Embassy Ilaje', 'pastor' => 'Deaconess Phil Ibrahim'],
            ['name' => 'Christ Embassy Thomas Estate', 'pastor' => 'Deacon Chiedu Ezemakam'],
            ['name' => 'Christ Embassy Owonikoko', 'pastor' => 'Brother Segun Agbeja'],
            ['name' => 'Christ Embassy General Paint', 'pastor' => 'Sister Efejiro Ben Edafe'],
            ['name' => 'Christ Embassy Agbationika Lane', 'pastor' => 'Pastor Nnenna Onyema'],
            ['name' => 'Christ Embassy Ajiwe Ilanla Church', 'pastor' => 'Sister Chidebere Okorie'],
            ['name' => 'Christ Embassy Ilaje Language Church', 'pastor' => 'Sister Perfect Egbafe'],
        ],
        'Mobil Road Group' => [
            ['name' => 'Christ Embassy Mobil Road Church', 'pastor' => 'Pastor Williams Adedeji'],
            ['name' => 'Christ Embassy Latest Base Church', 'pastor' => 'Brother Moses Ocheja'],
            ['name' => 'Christ Embassy Alaguntan Football Field Church', 'pastor' => 'Brother Godswill Egwu'],
            ['name' => 'Christ Embassy Success Community Church', 'pastor' => 'Brother Gowin Bassey'],
            ['name' => 'Christ Embassy Mobil Road Football Field Church', 'pastor' => 'Brother Godswill Egwu'],
            ['name' => 'Christ Embassy Idiroko Community Church', 'pastor' => 'Sister Ijeoma Azuamairo'],
            ['name' => 'Christ Embassy Maroko Community Church', 'pastor' => 'Sister Ruth Agbo'],
            ['name' => 'Christ Embassy Mazing Academy Church', 'pastor' => 'Sister Beauty Oghoyone'],
            ['name' => 'Christ Embassy Ikota Innercity School Church', 'pastor' => 'Sister Chinenye Olise'],
            ['name' => 'Christ Embassy Liberty School Church', 'pastor' => 'Sister Beauty Oghoyone'],
            ['name' => 'Christ Embassy Okun Ajah', 'pastor' => 'Deaconess Sandra Ademosun'],
            ['name' => 'Christ Embassy Okun Mopol', 'pastor' => 'Brother Emmanuel Atume'],
        ],
        'Ogombo Group' => [
            ['name' => 'Christ Embassy Ogombo Road', 'pastor' => 'Pastor Emeka Ogbonna'],
            ['name' => 'Christ Embassy Ogombo Central Church', 'pastor' => 'Sister Christy Eke'],
            ['name' => 'Christ Embassy Ogombo 2', 'pastor' => 'Deaconess Unoma Nwangoje'],
            ['name' => 'Christ Embassy Terra Annex', 'pastor' => 'Sister Efe Oyibo'],
            ['name' => 'Christ Embassy Hosanna School Church', 'pastor' => 'Sister Beauty Oghoyone'],
            ['name' => 'Christ Embassy Newtown', 'pastor' => 'Brother Chris Ukandu'],
        ],
        'Alasia Group' => [
            ['name' => 'Christ Embassy Alasia Ibeju-Lekki', 'pastor' => 'Pastor Femi Olushaki'],
            ['name' => 'Christ Embassy Gbetu', 'pastor' => 'Pastor Joy Oparaji'],
            ['name' => 'Christ Embassy Ogunfayo 2 Church', 'pastor' => 'Brother John Oparaji'],
            ['name' => 'Christ Embassy Ogidan', 'pastor' => 'Brother Ademola Odupitan'],
            ['name' => 'Christ Embassy Ologufe, Ibeju Lekki', 'pastor' => 'Deaconess Ruth Okhiku'],
            ['name' => 'Christ Embassy Charis Center', 'pastor' => 'Pastor Pope Obogai'],
            ['name' => 'Christ Embassy New Abijo Church', 'pastor' => 'Deacon Roman Asekutu'],
            ['name' => 'Christ Embassy Eko-Akete Church', 'pastor' => 'Sister Amara Ejiofobiri'],
            ['name' => 'Christ Embassy Elesekan', 'pastor' => 'Pastor Athoja Akalamudo'],
            ['name' => 'Christ Embassy Monastery Road Church', 'pastor' => 'Deacon Austin Nwaokolo'],
        ],
        'Tedo Group' => [
            ['name' => 'Christ Embassy Tedo', 'pastor' => 'Pastor Eva Olagbegi'],
            ['name' => 'Christ Embassy Fidiso', 'pastor' => 'Brother John Imo'],
            ['name' => 'Christ Embassy Miracle Avenue, Ogombo Road', 'pastor' => 'Brother Sunny Anyaeji'],
            ['name' => 'Christ Embassy Corner Bus Stop', 'pastor' => 'Brother Temitope Arogunyo'],
            ['name' => 'Christ Embassy United Estate Sangotedo', 'pastor' => null],
            ['name' => 'Christ Embassy Completeness Church', 'pastor' => 'Brother Prince Thompson'],
            ['name' => 'Christ Embassy Atlantic Estate Tedo', 'pastor' => 'Brother Chukwuemeka Okoronkwo'],
            ['name' => 'Christ Embassy Silverland', 'pastor' => 'Brother Osayomore Richards'],
            ['name' => 'Christ Embassy Online Church Dominica Island', 'pastor' => 'Pastor Eva Olagbegi'],
        ],
        'Abijo Sub-Group' => [
            ['name' => 'Christ Embassy Abijo', 'pastor' => 'Pastor Phillip Mordi'],
            ['name' => 'Christ Embassy Majek', 'pastor' => 'Deaconess Tolu Koko'],
        ],
        'Kajola Group' => [
            ['name' => 'Christ Embassy Kajola Lakowe', 'pastor' => 'Pastor Val Odili'],
            ['name' => 'Christ Embassy Peru', 'pastor' => 'Pastor Val Odili'],
            ['name' => 'Christ Embassy Onosa', 'pastor' => 'Sister Christy'],
            ['name' => 'Christ Embassy Ibeju-Agbe', 'pastor' => 'Brother Gbenga Kumayon'],
            ['name' => 'Christ Embassy Igbojia', 'pastor' => 'Deaconess Judith Okonu'],
            ['name' => 'Christ Embassy Dessa Church', 'pastor' => 'Sister Wealth Okposio'],
            ['name' => 'Christ Embassy Dessa Church II', 'pastor' => 'Brother Raphael Akan'],
            ['name' => 'Christ Embassy Ayeteju', 'pastor' => 'Brother Chijoke Ozojiofor'],
            ['name' => 'Christ Embassy Ighando Church', 'pastor' => 'Brother Michael Akpeohai'],
            ['name' => 'Christ Embassy Alugba Church', 'pastor' => 'Sister Bernice Akalamudo'],
        ],
        'Onishon Group' => [
            // Group pastor: Pastor Charis Onuoha — not a church.
            ['name' => 'Christ Embassy Onishon', 'pastor' => 'Pastor Iyobosa Iyamu'],
            ['name' => 'Christ Embassy Bogije', 'pastor' => 'Sister Helen Abu'],
            ['name' => 'Christ Embassy Oribanwa', 'pastor' => 'Deaconess Chito-Ike-Mgbechi'],
            ['name' => 'Christ Embassy Adeba', 'pastor' => 'Brother Emeka Dennis Okeawolam'],
            ['name' => 'Christ Embassy Ogunfayo', 'pastor' => 'Sister Emelda Nkiru Eziokwu'],
            ['name' => 'Oribanwa Phase 2 SC', 'pastor' => 'Brother Chris Monye'],
        ],
        'Eputu Group' => [
            // Group pastor: Pastor Femi Olushakin — not a church.
            ['name' => 'Christ Embassy Eputu', 'pastor' => 'Brother Raymond Babalola'],
            ['name' => 'Christ Embassy Awoyaya', 'pastor' => 'Brother Onyeka Ibenyi'],
            ['name' => 'Christ Embassy Eputu 2', 'pastor' => 'Brother Matthew Olabamiji'],
            ['name' => 'Christ Embassy Labora', 'pastor' => 'Sister Glory Udok'],
            ['name' => 'Christ Embassy Parapo', 'pastor' => 'Brother Kenneth Okojie'],
            ['name' => 'Christ Embassy Global', 'pastor' => 'Sister Faith Ubaka'],
            ['name' => 'Christ Embassy Cele Imedu', 'pastor' => 'Brother Matthew Olabamiji'],
            ['name' => 'Christ Embassy Garrison', 'pastor' => 'Brother Chris Johnson'],
            ['name' => 'Christ Embassy Genesis', 'pastor' => 'Sister Blessing Ejeh'],
        ],
        'Epe Group' => [
            ['name' => 'Christ Embassy Epe Central', 'pastor' => 'Pastor Toro Bank-Omotoye'],
            ['name' => 'Christ Embassy Hospital Road, Epe', 'pastor' => 'Brother Collins Osagie'],
            ['name' => 'Christ Embassy Marina Epe', 'pastor' => 'Brother Prince Aideyan'],
            ['name' => 'Christ Embassy Temu Epe', 'pastor' => 'Pastor Christopher Ukeka'],
            ['name' => 'Christ Embassy Eredo Epe', 'pastor' => 'Brother Stephen Olalekan'],
            ['name' => 'Christ Embassy Omu Epe', 'pastor' => 'Brother David Elijah'],
            ['name' => 'Christ Embassy Mojoda', 'pastor' => 'Brother Daniel Odofin'],
            ['name' => 'Christ Embassy Iraye Epe', 'pastor' => 'Pastor Felicia Onawoga'],
            ['name' => 'Christ Embassy Papa', 'pastor' => 'Brother Samson Monday'],
            ['name' => 'Christ Embassy Igodu Epe', 'pastor' => 'Sister Ronke Adedeji'],
            ['name' => 'Christ Embassy Odomola', 'pastor' => 'Sister Simisola Osagie'],
            ['name' => 'Christ Embassy Shala', 'pastor' => 'Brother Taiwo Soteye'],
        ],
        'Brazil Group' => [
            ['name' => 'Christ Embassy Guarapuava 1', 'pastor' => 'Brother Rodrigo Rodrigo'],
            ['name' => 'Christ Embassy Guarapuava 2', 'pastor' => 'Brother Agenor Chies'],
            ['name' => 'Christ Embassy Southern Brazil', 'pastor' => 'Sister Martins Jennifer'],
            ['name' => 'Christ Embassy Alagoes', 'pastor' => 'Sister Isabella Loped'],
        ],
        'Youth Church Group' => [
            ['name' => 'Christ Embassy Lekki Youth Church', 'pastor' => 'Pastor Eva Iyeke'],
            ['name' => 'Christ Embassy Victoria Island Youth Church', 'pastor' => 'Pastor Philemon Tsewinor'],
            ['name' => 'Christ Embassy Ajah Youth Church', 'pastor' => 'Sister Esther Okpara'],
            ['name' => 'Christ Embassy Ogombo Youth Church', 'pastor' => 'Brother Julius Zion'],
            ['name' => 'Christ Embassy Charis Center Youth Church', 'pastor' => 'Pastor Cindy Nnodim'],
            ['name' => 'Christ Embassy Gbetu Youth Church', 'pastor' => 'Sister Jennifer Ekene'],
            ['name' => 'Christ Embassy Kajola Youth Church', 'pastor' => 'Pastor Ebuka Ike-Mgbechi'],
            ['name' => 'Christ Embassy Badore Youth Church', 'pastor' => 'Sister Edema Duke'],
            ['name' => 'Christ Embassy Onosa Youth Church', 'pastor' => 'Brother Gideon Aniechi'],
            ['name' => 'Christ Embassy Awoyaya Youth Church', 'pastor' => 'Sister Precious Lambano'],
            ['name' => 'Christ Embassy Brazil Youth Church', 'pastor' => 'Brother Cesar Antonio'],
            ['name' => 'Christ Embassy Bogije Youth Church', 'pastor' => 'Brother Emmanuel Odey'],
            ['name' => 'Christ Embassy Eputu Youth Church', 'pastor' => 'Sister Margaret Godwin'],
            ['name' => 'Christ Embassy Keffi Youth Church', 'pastor' => 'Sister Blessing Ndoma'],
        ],
        'Teens Church Group' => [
            ['name' => 'Christ Embassy Lekki Teens Church', 'pastor' => 'Deaconess Sandrah Ademosun'],
            ['name' => 'Christ Embassy Lekki Sunrise Teens Church', 'pastor' => 'Sis Obehi Aigbefoh'],
            ['name' => 'Christ Embassy Ajah Teens Church', 'pastor' => 'Sis Chimnedu Okorie'],
            ['name' => 'Christ Embassy Lekki Community Church', 'pastor' => 'Sis Adetutu Falade'],
            ['name' => 'Christ Embassy Kajola Teens Church', 'pastor' => 'Sister Eki Asemota'],
            ['name' => 'Christ Embassy Ikoyi Teens Church', 'pastor' => 'Sister Favour Mbalu'],
            ['name' => 'Christ Embassy Lagos Island Teens Church', 'pastor' => 'Brother Zoe Ezeifedi'],
            ['name' => 'Christ Embassy Tedo Teens Church', 'pastor' => 'Brother Praise Imo'],
            ['name' => 'Christ Embassy Mobil Road Teens Church', 'pastor' => 'Sister Sarah Bassey'],
            ['name' => 'Christ Embassy Epe Teens Church', 'pastor' => 'Brother Muyiwa Orojo'],
            ['name' => 'Christ Embassy Victoria Island Teens Church', 'pastor' => 'Sister Nini Bank-Omotoye'],
            ['name' => 'Christ Embassy Victoria Island Church 2 Teens', 'pastor' => 'Brother Salem Sunny'],
            ['name' => 'Christ Embassy Chevron Teens Church', 'pastor' => 'Sister Sarah Afolabi'],
            ['name' => 'Christ Embassy Ogombo 1 Teens Church', 'pastor' => 'Sister Angel Odiale'],
            ['name' => 'Christ Embassy Ajiwe Teens Church', 'pastor' => 'Bro David Egbetola'],
            ['name' => 'Christ Embassy Gbetu Teens Church', 'pastor' => 'Bro Faith Idowu'],
            ['name' => 'Christ Embassy Alasia Teens Church', 'pastor' => 'Pst Respect Obim'],
        ],
    ];

    public function run(): void
    {
        $credentials = [];
        $defaultPassword = env('SEED_CHURCH_ADMIN_PASSWORD'); // null = generate a random one per church

        foreach (self::GROUPS as $groupName => $churches) {
            $group = GroupChurch::updateOrCreate(['name' => $groupName], ['name' => $groupName]);

            foreach ($churches as $churchData) {
                $church = Church::updateOrCreate(
                    ['name' => $churchData['name'], 'group_church_id' => $group->id],
                    ['pastor_name' => $churchData['pastor']]
                );

                $email = Str::slug($churchData['name']).'@zone5.app';
                $password = $defaultPassword ?: Str::random(12);

                User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $churchData['pastor'] ?? $churchData['name'],
                        'password' => Hash::make($password),
                        'role' => 'church_admin',
                        'church_id' => $church->id,
                    ]
                );

                $credentials[] = [
                    'group' => $groupName,
                    'church' => $churchData['name'],
                    'pastor' => $churchData['pastor'],
                    'email' => $email,
                    'password' => $password,
                ];
            }
        }

        // Write every generated login to a CSV for handoff — printing 240+
        // lines to the console isn't practical, and re-running the seeder
        // won't reveal old plaintext passwords again (only the hashes are
        // stored), so this file is the only place the passwords exist
        // outside of what you hand out to each pastor.
        $csv = "group,church,pastor,email,password\n";
        foreach ($credentials as $row) {
            $csv .= implode(',', array_map(
                fn ($v) => '"'.str_replace('"', '""', $v ?? '').'"',
                [$row['group'], $row['church'], $row['pastor'], $row['email'], $row['password']]
            ))."\n";
        }
        Storage::disk('local')->put('church-logins.csv', $csv);

        $this->command?->info(count($credentials).' churches seeded across '.count(self::GROUPS).' groups.');
        $this->command?->info('Login credentials written to storage/app/church-logins.csv — treat this file as sensitive and delete it once distributed.');
    }
}