<?php
/**
 * Created by OxGroup.
 * User: aliaxander
 * Date: 15.08.15
 * Time: 15:16
 */

namespace Acme\Console\Command;

use Faker\Factory;
use OxApp\helpers\FreenomReg;
use OxApp\helpers\IgApi;
use OxApp\helpers\Resize;
use OxApp\models\Domains;
use OxApp\models\HashTags;
use OxApp\models\ProfileGenerate;
use OxApp\models\SystemSettings;
use OxApp\models\Users;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CronCommands
 *
 * @package Acme\Console\Command
 */
class EditProfile extends Command
{
    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('edit:start')
            ->setDescription('Cron jobs');
    }
    
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        require(__DIR__ . "/../../config.php");
        if (SystemSettings::get('hashTagSpam') == 1) {
            $hashTags = HashTags::find();
            $hashTagsResult = [];
            foreach ($hashTags->rows as $tag) {
                $hashTagsResult[] = $tag->tag;
            }
        }
        $status = true;
        while ($status = true) {
            $users = Users::orderBy(['userName' => 'desc'])->limit([0 => 1])->find([
                'ban' => 0,
                'userTask' => 2,
                'login' => 0
            ]);
            if ($users->count > 0) {
                $limitAccounts = SystemSettings::get('countThreadsForSubnet');
                $user = $users->rows[0];
                $proxy = explode(":", $user->proxy);
                $findUsers = Users::find([
                    'ban' => 0,
                    'userTask' => 3,
                    'login/in' => [0, 1],
                    'proxy/like' => $proxy[0] . ":%"
                ]);
                if ($findUsers->count >= $limitAccounts) {
                    echo "\nLimit account. {$findUsers->count} >= {$limitAccounts} Search other account:\n";
                    $users = Users::orderBy(["id" => 'desc'])->limit([0 => 1])->find([
                        'ban' => 0,
                        'userTask' => 3,
                        'login' => 0,
                        'proxy/not like' => $proxy[0] . ":%"
                    ]);
                    $user = @$users->rows[0];
                    $proxy = explode(":", @$user->proxy);
                    if (empty($user)) {
                        die('No users');
                    }
                    $findUsers = Users::find([
                        'ban' => 0,
                        'userTask' => 3,
                        'login/in' => [0, 1],
                        'proxy/like' => $proxy[0] . ":%"
                    ]);
                    if ($findUsers->count >= $limitAccounts) {
                        die('Wait limit subnet ip');
                    }
                }
                if (!empty($user)) {
                    if (SystemSettings::get('hashTagSpam') == 0) {
                        Users::where(['id' => $user->id])->update(['userTask' => 3]);
                    } else {
                        Users::where(['id' => $user->id])->update(['userTask' => 9]);
                    }
                    
                    $dir = scandir('/home/photos');
                    unset($dir[array_search('.', $dir)]);
                    unset($dir[array_search('..', $dir)]);
                    $dir = array_values($dir);
                    
                    $api = new IgApi();
                    $api->proxy = $user->proxy;
                    $api->username = $user->userName;
                    $loginResult = '';
                    $i = 0;
                    while ($loginResult === '') {
                        $login = $api->login($user->guid, $user->phoneId, $user->deviceId, $user->password);
                        $loginResult = @$login[1];
                        if ($i === 5) {
                            $loginResult = false;
                        }
                        $i++;
                    }
                    
                    //SetPhoto:
                    $photoResult = '';
                    $result = true;
                    $i = 0;
                    while ($photoResult === '') {
                        $photo = '/home/photos/' . $dir[rand(0, count($dir) - 1)];
                        $result = $api->changeProfilePicture($photo);
                        $photoResult = $result[1];
                        if ($i === 5) {
                            $photoResult = false;
                        }
                        $i++;
                    }
                    if (@$result[1]['message'] === 'checkpoint_required' && !empty($user->id)) {
                        Users::where([
                            'id' => $user->id
                        ])->update(['ban' => 1]);
                        die("Account banned");
                    }
                    
                    //unlink($photo);
                    
                    $profiles = ProfileGenerate::limit([0 => 1])->find(['status' => 0])->rows[0];//groupBy('description')->
                    ProfileGenerate::where(['id' => $profiles->id])->update(['status' => 1]);
                    //            $word = [$user->userName, $user->firstName, mt_rand(10000, 99999)];
                    //            $word = $word[mt_rand(0, 2)];
                    //            $word = str_replace([' ', '.'], '', $word);
                    $biography = @$profiles->description;
                    //            $url = mb_strtolower(str_replace('%username%', $word, $profiles->url));
                    //            //
                    //            //
                    //                $result = FreenomReg::freedomReg($profiles->url);
                    //                $p = xml_parser_create();
                    //                xml_parse_into_struct($p, $result[1], $vals, $index);
                    //                xml_parser_free($p);
                    //                $domain = mb_strtolower($vals[2]['value']);
                    //                if (rand(0, 1) == 1) {
                    //                    $domain = "http://" . $domain;
                    //                }
                    
                    //$faker = Factory::create();
                    $domain = Domains::orderBy(['id' => 'asc'])->limit([0 => 1])->find(['status' => 0]);
                    if ($domain->count == 1) {
                        Domains::where(['id' => $domain->rows[0]->id])->update(['status' => 1]);
                        $domain = $domain->rows[0]->domain;
                        //                        if (rand(0, 1) == 1) {
                        //                            $domain = "http://" . $domain;
                        //                        }
                        $domain = str_replace([" ", "\n", "\r", "\t"], "", $domain);
                        $profileResult = '';
                        $i = 0;
                        while ($profileResult === '') {
                            //$biography
                            //$domain = $domain->rows[0]->domain;
                            // $domains = ['.myblogonline.pw', '.blogonline.pw'];
                            //$domain = $faker->userName . rand(1950, 2017) . '.love2live2.com ';
                            //                    $domain = str_replace(" ", "", $domain);
                            //                    $domain = str_replace("'", "", $domain);
                            //                    $domain = mb_strtolower($domain);
                            //                    $result = FreenomReg::freedomReg($domain);
                            //                    $p = xml_parser_create();
                            //                    xml_parse_into_struct($p, $result[1], $vals, $index);
                            //                    xml_parser_free($p);
                            //                    $domain = mb_strtolower($vals[2]['value']);
                            //                    if (rand(0, 1) == 1) {
                            //                        $domain = "http://" . $domain;
                            //                    }
                            
                            $profile = $api->oldEdit($biography, $domain, $user->phoneId, $user->firstName,
                                $user->email);
                            $profileResult = $profile[1];
                            if (empty($profile[1])) {
                                Domains::where(['domain' => $domain])->update(['status' => 0]);
                                $profileResult = '';
                            }
                            if ($i === 5) {
                                $profileResult = false;
                            }
                            $i++;
                        }
                        
                        print_r($profile);
                        
                        if (SystemSettings::get('uploadPhotos') == 1) {
                            $dir = scandir('/home/feedPhoto/');
                            unset($dir[array_search('.', $dir)]);
                            unset($dir[array_search('..', $dir)]);
                            $dir = array_values($dir);
                            
                            $randCount = mt_rand(1, 2);//SystemSettings::get('countFeedPhoto')
                            for ($i = 0; $i < $randCount; $i++) {
                                $file = rand(0, count($dir) - 1);
                                $resize = new Resize();
                                $photo = '/home/feedPhoto/' . $dir[$file];
                                
                                $magic = new \Imagick();
                                $magic->readimage($photo);
                                $magic->flopImage();
                                $magic->blurImage(0, rand(7, 10));
                                $magic->rotateImage('rgba(' . rand(0, 255) . ', ' . rand(0, 255) . ', ' . rand(0,
                                        255) . ')', rand(0, 5));
                                // Создать новый шаблон
                                $draw = new \ImagickDraw();
                                
                                // Свойства шрифта
                                //$draw->setFont('Arial');
                                $draw->setFontSize(rand(40, 90));
                                $draw->setFillColor('rgba(' . rand(0, 255) . ', ' . rand(0, 255) . ', ' . rand(0,
                                        255) . ')');
                                
                                // Положение текста
                                $draw->setGravity(\Imagick::GRAVITY_SOUTHEAST);
                                
                                // Нарисовать текст на изображении
                                $magic->annotateImage($draw, rand(10, 150), rand(12, 160), 0, $domain);
                                
                                $magic->writeimage($photo);
                                
                                $photo = $resize->check('/home/feedPhoto/' . $dir[$file]);//$photo
                                unset($dir[$file]);
                                $dir = array_values($dir);
                                
                                print_r($media = $api->uploadPhoto($photo));
                                
                                $media_id = $media[1]['upload_id'];
                                /*
Список хэштегов частично забаненых в Инстаграм

    #adulting
    #alone
    #americangirl
    #ariefmirna2015
    #armparty
    #asia
    #asiandick
    #attractive
    #babyrp
    #bacak
    #badbitcztwerk
    #baddie
    #balenciaga
    #balls
    #bang
    #bangbang
    #batikate
    #beaty
    #belfie
    #bi
    #bigdickboy
    #bikinibody
    #bombshell
    #bootay
    #bootybounce
    #bra
    #brain
    #breast
    #buns
    #butt
    #butts
    #cam
    #carving
    #catsau
    #cesitone
    #cheeky
    #citycentre
    #commentschivettes
    #costumes
    #cph
    #cpr
    #csun
    #cumfession
    #curvesfordays
    #curvy
    #damngirl
    #datass
    #date
    #dating
    #desk
    #direct
    #dm
    #dominant
    #dripping
    #dutchgirl
    #dxb
    #easter
    #ebonyandivory
    #edm
    #edmbabes
    #eggplant
    #eggplants
    #eggporn
    #elevator
    #escilepernatale
    #estellaseraphim
    #everybodyisbeautiful
    #excitada
    #expose
    #fapstagram
    #feetofatlanta
    #fishnets
    #foreplay
    #freakshow
    #freethenipple
    #gays
    #gilofashion
    #girlsonly
    #gloves
    #goddess
    #hamishnadine
    #happythanksgiving
    #hardsummer
    #hijabiba
    #hooters
    #hornyyyyyyasf
    #hotgirls
    #hotguy
    #hots
    #hottie
    #humpday
    #iamgay
    #instagirl
    #instamood
    #istanbulgay
    #italiano
    #jugs
    #kansas
    #kickoff
    #kik
    #kikgirl
    #kikmessenger
    #killingit
    #kissing
    #lesbian
    #lesbiansofinstagram
    #lilmandingo
    #lingerie
    #lust
    #marcoreus
    #master
    #mebelim
    #medicina
    #mexicangirl
    #mirrorphoto
    #mixedgirls
    #models
    #mr40club
    #mrsandmrsbordeaux
    #mrtox
    #nacket
    #nasty
    #newyears
    #newyearsday
    #ngento
    #oovoo
    #petite
    #piroka
    #pixie
    #poop
    #pornfood
    #printic
    #publicrelations
    #pushups
    #rack
    #ravebabes
    #roleplay
    #russiangirl
    #russianmilf
    #saltwater
    #sexlife
    #shebad
    #shesquats
    #shit
    #shower
    #single
    #singlelife
    #skype
    #slimthick
    #snap
    #snapback
    #snapchat
    #snapchatgay
    #snapme
    #sokus
    #sopretty
    #spanishgirl
    #sparklingnudes
    #stopdropandyoga
    #stranger
    #streetphoto
    #stud
    #submission
    #sultry
    #sunbathing
    #swole
    #tag4like
    #takeitoff
    #teens
    #tgif
    #thatasstho
    #thick
    #thought
    #todayimwearing
    #treviso
    #twerk
    #twerker
    #undies
    #valentinesday
    #vatine
    #weed
    #weedstagram
    #weezmoney
    #wet
    #whitegirl
    #woman
    #womancrushwednesday
    #women
    #workflow

Список хэштегов полностью заблокированных в Инстаграм

    #anal
    #anon
    #arse
    #ballsack
    #bigboobs
    #bigbootyhoe
    #bigtits
    #bigtitties
    #bitches
    #blowjob
    #bondagesex
    #boobies
    #boobstagram
    #boobz
    #booty
    #bunda
    #clevage
    #clit
    #cock
    #cocks
    #cum
    #cumshot
    #cunt
    #daddydirection
    #daddydom
    #daddylove
    #dick
    #dicks
    #faggot
    #fatass
    #femdom
    #fetish
    #footfetish
    #freethenips
    #frisky
    #fucking
    #funbags
    #girlsgonewild
    #girlsofinstagram
    #grindin
    #gstring
    #hardcore
    #hmu
    #hoes
    #horny
    #IG
    #ilovemyinstagram
    #incest
    #instababe
    #instabody
    #instafuck
    #instahot
    #instasex
    #jizz
    #kike
    #kikmeboys
    #kikmenow
    #killyourself
    #kinky
    #kontol
    #kyke
    #lean
    #like
    #loseweight
    #naughtygirls
    #nekkid
    #nigger
    #nipple
    #nipples
    #nips
    #nobra
    #nudepics
    #orgasm
    #penis
    #popular
    #popularpage
    #porn
    #porno
    #pornography
    #pornstar
    #proanorexia
    #probulimia
    #seksi
    #selfharm
    #sexiest
    #sexual
    #sexyaf
    #sexybody
    #sexygirl
    #sexys
    #sexyteen
    #shirtless
    #slut
    #sluts
    #snowbunnies
    #spankme
    #spic
    #tetas
    #thickgirls
    #thighs
    #thinspiration
    #thinspo
    #tit
    #tits
    #titties
    #titty
    #toket
    #twerking
    #twerkteam
    #twink
    #vag
    #wank
    #whitepower
    #whore
    #whores
    #wtf
    #xxx
    #おっぱい
*/
                                $caption = '';
                                if (SystemSettings::get('hashTagSpam') == 1) {
                                    $hashTags = HashTags::find();
                                    $hashTagsResult = [];
                                    foreach ($hashTags->rows as $tag) {
                                        $hashTagsResult[] = $tag->tag;
                                    }
                                    $caption = '#' . $hashTagsResult[mt_rand(0,
                                            count($hashTagsResult) - 1)] . ' #' . $hashTagsResult[mt_rand(0,
                                            count($hashTagsResult) - 1)] . ' #' . $hashTagsResult[mt_rand(0,
                                            count($hashTagsResult) - 1)];
                                }
                                $data = [
                                    'device_id' => $user->deviceId,
                                    'guid' => $user->guid,
                                    'media_id' => "$media_id",
                                    'caption' => $caption,
                                    'device_timestamp' => "" . time() . "",
                                    'source_type' => '5',
                                    'filter_type' => '0',
                                    'extra' => '{}',
                                    "Content-Type" => "application/x-www-form-urlencoded; charset=UTF-8"
                                
                                ];
                                $data = json_encode($data);
                                
                                $arr = $api->request('media/configure/', $data);
                                print_r($arr);
                                // unlink($photo);
                                sleep(rand(5, 10));
                            }
                        }
                        if (!empty($user->id)) {
                            Users::where(['id' => $user->id])->update([
                                'login' => 1,
                                'biography' => $biography,
                                'url' => $domain,
                                'photo' => $profile[1]['user']['profile_pic_url']
                            ]);
                        }
                    } else {
                        die('no domains');
                    }
                } else {
                    die('no tasks');
                }
            } else {
                die('no tasks');
            }
        }
        
        //EditProfile
        return $output->writeln("Complite");
    }
}
