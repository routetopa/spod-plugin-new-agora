<?php
/**
 * Created by PhpStorm.
 * User: rended
 * Date: 22/05/17
 * Time: 12:38
 */

$url = 'http://172.16.15.128/agora/ajax/add-comment-test/';
$referrer = 'http://172.16.15.128/agora/17';
$user_agent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.100 Safari/537.36';




$ch = curl_init($url);

curl_setopt($ch, CURLOPT_HTTPHEADER,
    array(
        'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
        'Connection: Keep-Alive',
        'Host: 172.16.15.128',
        'Origin: http://172.16.15.128',
        'X-Requested-With: XMLHttpRequest'
    ));

curl_setopt($ch, CURLOPT_COOKIE,'XDEBUG_SESSION=PHPSTORM;token=t.2VQW3d8F8J1Nys0SGpNx;0ba237c9478e389a389d55c74d7174d2=pco9kncs6uqq04c6m1e8bh45q3;language=en-gb;express_sid=s%3AI9boajqGGxA9zH5yxsMgWlgppsigzLeA.sYQ0E4wQuYDYs0VIYkJKg1NyB74THm0yZQJFz7xSk%2Fg;test=test;i18next=en-GB;adminToken=WatiLYGu9Orer3JEtIK1guBYqymEGugi;ow_login=237633b589ebbf3d6b402ea5d3b9fdfe;base_language_id=1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
curl_setopt($ch, CURLOPT_REFERER, $referrer);
curl_setopt($ch,CURLOPT_USERAGENT, $user_agent);

curl_setopt($ch, CURLOPT_VERBOSE, true);
//$res = curl_exec($ch);
//$retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//curl_close($ch);

//print_r($res);

$count = 10000;

$room = 17;

$component = [urlencode('barchart-datalet'), 'leafletjs-datalet', '', ''];
$params = [urlencode('{"data-url":"http://172.16.15.128/cocreation/ajax/get-dataset-by-room-id-and-version/?room_id=22&version=3","selectedfields":"[{\"field\":\"XAxis\",\"value\":\"cos\",\"index\":2},{\"field\":\"NumericYAxis\",\"value\":\"n\",\"index\":2},null,null,null,{\"field\":\"Categories\",\"value\":\"cat\",\"index\":1}]","filters":"[]","aggregators":"[]","orders":"[]","x-axis-label":"","y-axis-label":"","suffix":"","legend":"topRight","data-labels":"true","stack":"false","theme":"themeBase"}'),
            urlencode('{"data-url":"http://prato.routetopa.eu/cocreation/ajax/get-dataset-by-room-id-and-version/?room_id=66&version=13","selectedfields":"[{\"field\":\"Latitude\",\"value\":\"Posizione\",\"index\":3},{\"field\":\"Longitude\",\"value\":\"Posizione\",\"index\":3},{\"field\":\"BalloonContent\",\"value\":\"Luogo\",\"index\":1},{\"field\":\"BalloonContent\",\"value\":\"Proponente\",\"index\":4},{\"field\":\"BalloonContent\",\"value\":\"Motivazione\",\"index\":2}]","filters":"[]","aggregators":"[]","orders":"[]","datalettitle":"Prato Wi-Fi extension","description":"- new antennas proposed by citizens"}'),
                     '',
                     ''
];
$data = [urlencode('[{"name":"cos","data":["a","b","c","d","e","f","g","h","i","l","m","n","o","p","q","r","s","t","u","v"]},{"name":"n","data":[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20]},{"name":"cat","data":[1,1,1,1,1,1,1,2,2,2,2,2,2,3,3,3,3,3,3,3]}]'),
    urlencode('[{"name":"Posizione","data":["43.88566285371781,11.097497642040253","43.899771, 11.071782","43.8956239,11.0663981","43.8853429,11.0623031","43.8875584,11.0565351","43.88049691915513,11.092267334461212","43.90030485,11.103638505120639","43.86408984661102,11.11819088459015","43.8631292,11.1093885","43.89813780784607,11.109269857406616","43.8594741,11.1073721","43.89251857995987,11.104160249233246","43.89346271753311,11.09356015920639","43.87732855975628,11.093519255518913","43.8801559,11.0891938","43.87610346078873,11.098270118236542","43.880055, 11.095297","43.8999,11.0519873","43.876641, 11.09287243.876641, 11.092872","43.8800267,11.0972221","43.87727156281471,11.072288900613785","43.88963252305985,11.052884459495544","43.888543546199806,11.047632694244385","43.88654798269272,11.059423685073853","43.885841220617294,11.048625111579895","43.888307511806495,11.056548357009888","43.91544073820114,11.097680032253265","43.86005848646165,11.113832294940948","43.90640705823899,11.115237772464752","43.90631586313248,11.114663779735567","43.89706492424011,11.107789278030396","43.90658525,11.112728792309575","43.88539731502533,11.071026921272278","43.886695504188545,11.073279976844788"]},{"name":"Posizione","data":["43.88566285371781,11.097497642040253","43.899771, 11.071782","43.8956239,11.0663981","43.8853429,11.0623031","43.8875584,11.0565351","43.88049691915513,11.092267334461212","43.90030485,11.103638505120639","43.86408984661102,11.11819088459015","43.8631292,11.1093885","43.89813780784607,11.109269857406616","43.8594741,11.1073721","43.89251857995987,11.104160249233246","43.89346271753311,11.09356015920639","43.87732855975628,11.093519255518913","43.8801559,11.0891938","43.87610346078873,11.098270118236542","43.880055, 11.095297","43.8999,11.0519873","43.876641, 11.09287243.876641, 11.092872","43.8800267,11.0972221","43.87727156281471,11.072288900613785","43.88963252305985,11.052884459495544","43.888543546199806,11.047632694244385","43.88654798269272,11.059423685073853","43.885841220617294,11.048625111579895","43.888307511806495,11.056548357009888","43.91544073820114,11.097680032253265","43.86005848646165,11.113832294940948","43.90640705823899,11.115237772464752","43.90631586313248,11.114663779735567","43.89706492424011,11.107789278030396","43.90658525,11.112728792309575","43.88539731502533,11.071026921272278","43.886695504188545,11.073279976844788"]},{"name":"Luogo","data":["Piazza G. Ciardi","Giardini di Maliseti tra via Kuliscioff e via Santanna di Stazzema","Piazza Giosuè Borsi","Impianti sportivi Luca Conti","Biblioteca comunale Circoscrizione Ovest","Piazza San Niccolò","Piazza Milton Nesi","Giardini di Viale Montegrappa","Piazza Falcone e Borsellino","Bocciodromo Santa Lucia","Piazzale del Museo","Ciclabile Viale Galilei","Giardini di Via Baracca","Giardini di SantOrsola","Porta Leone","Porta Frascati","Via Ser Lapo Mazzei co. Archivio di Stato di Prato, Istituto Internazionale di Storia Economica \"F.Datini\",Centro Studi Storici Postali, nonché Casa Museo Francesco Datini","Giardini Pubblici Via Anita Garibaldi - Viaccia","Zappa! Resistenza creativa via Carradori 12","Giardino Buonamici","Passerella Via Rimini","Giardini via Mediterraneo","Giardino via Brasimone","Giardino via Valori","Giardino Via Caciolli","P.za della Chiesa di Galciana","Casa Museo Leonetto Tintori","Giardini pubblici Via Picasso","Circolo ARCI Santa Lucia","Bar Moncelli Santa Lucia","Parco degli Ulivi Santa Lucia","Chiesa Santa Lucia","Giardini Chiesino di San Paolo","Giardini Via Vivaldi"]},{"name":"Proponente","data":["Riccardo Petrocchi","Simone Ferri","Enrico Querci","Elena Palmisano","Elena Palmisano","Enrico Querci","Enrico Querci","Riccardo Petrocchi","Riccardo Petrocchi","Riccardo Petrocchi","Massimiliano Giunta","Riccardo Petrocchi, Massimiliano Giunta","Massimiliano Giunta","Massimiliano Giunta","Massimiliano Giunta","Massimiliano Giunta","Mario Bettocchi","Daniele Santini","Pasquale Scalzi","Stefano Ortenzi","Camilla Galli","Sara Pescioni","Sara Pescioni","Sara Pescioni","Sara Pescioni","Sara pescioni","Sergio La Porta","Katia Corradi","Maria Grazia Tempesti","Maria Grazia Tempesti","Maria Grazia Tempesti","Maria Grazia Tempesti","Federico Logli","Federico Logli"]},{"name":"Motivazione","data":["E una delle piazze che lamministrazione sta riqualificando","Si tratta di un luogo pubblico al momento non coperto ne dalla rete del Comune, ne da quella della Provincia. Sarebbe molto utile perché vi sono molti punti di aggregazione intorno: il bocciodromo (uno dei più frequentati di Prato), la Conad, e le nuove scuole medie di via Isola di Leno.","Una delle piazze che il Comune sta rifacendo, in una zona (Narnali) dove attualmente non risultano postazioni wifi pubbliche","Impianto sportivo importante nella zona di Galciana","La biblioteca comunale costituisce un attrattore per la popolazione del quartiere.","La piazza è in rifacimento ed attualmente lantenna più vicina è quella presso la Rete Civica in Via S. Caterina. Vista la presenza della scuola, una maggiore copertura potrebbe essere utile","E una delle piazze che il comune sta riqualificando, è vicina a moltissimi centri di aggregazione quali campi sportivi, circolo e misericordia, quindi punto strategico nel cuore del quartiere","Uno dei giardini più frequentati nella zona di Mezzana.","Area verde nelle vicinanze del Tribunale, quindi molto frequentata","Zona ricreativa collegata alla pista ciclabile","Si trova vicino a McDrive e da lì partono gli autobus di  FlixBUs, è quindi una zona molto frequentata","La copertura wifi della ciclabile può essere molto utile a chi fa sport e a chi si sposta in bicletta","Luogo di aggregazione sociale","Luogo molto frequentato, dove si organizzano molti eventi soprattutto estivi","Ci sono molte fermate degli autobus e la sede del centro di prevenzione Eliana Monarca","Vicino allaccesso alla Biblioteca Lazzerini, zona verde","Frequentato da ricercatori e studenti di tutte le nazionalità. Spesso in difficoltà (economica e tecnica) di connessione","Estrema periferia ovest della città, dove è presente un giardino pubblico e relativo campo di calcio, molto frequentato da ragazzi e adulti della zona. Punto di ritrovo importante sopratutto in primavera ed in estate","Zappa! è un collettivo che realizza progetti ed eventi, con lo scopo di fare esperienza della cultura dal basso e promuoverla.\n\nZappa! è anche un luogo in cui creare e diffondere l’arte come artigianato e come frutto di connessioni e di incontri tra persone.\n\nZappa! è un luogo in cui immaginare e sognare\n\nZappa! nasce come laboratorio sociale in divenire, territorio in cui far convergere le idee e le competenze esterne, creare connessioni e trasformarle al suo interno, creando dei percorsi artistici e umani nuovi.","Giardino sede di numerose manifestazioni e di grande frequentazione da parte dei cittadini","Sede di fermate LAM e altri bus nei pressi del polo scolastico di Reggiana, molto frequentata dagli studenti","sono lughi molto frequentati da ragazzi e da genitori che accompagnao i figli","sono lughi molto frequentati da ragazzi e da genitori che accompagnao i figli","sono lughi molto frequentati da ragazzi e da genitori che accompagnao i figli","sono luoghi molto frequentati da ragazzi e da genitori che accompagnano i figli","luogo di aggregazione della comunità","Il Museo ed il parco ospitano molte opere di Leonetto Tintori e qui si svolgono attività culturali a cura dellassociazione \"Laboratorio per affresco Elena e Leonetto Tintori\"","Luogo di ritrovo del quartiere","Luogo di ritrovo","La zona è un luogo di ritrovo molto frequentato ed il Bar Moncelli costituisce un forte polo di attrazione.","Uno dei parchi più frequentati della città, dove è presente anche il Bocciodromo","Luogo di aggregazione, frequentato dai partecipanti alle iniziative religiose e sociali della parrocchia","Luogo di aggregazione sociale frequentato dalle persone del quartiere anche per la presenza del chiesino.","Luogo di aggregazione sociale frequentato dalle persone del quartiere"]}]'),
                    '',
                    ''
];

$username = ['Mario', 'Il bimbo Gigi', 'Bruno Vespa', 'Anip'];
$avatar = ['Mario', 'Il bimbo Gigi', 'Bruno Vespa', 'Anip'];


for($i = 0; $i < $count; $i++) {
    $index = rand(0, 3);
//    $index = 1;
    $fields = array(
        'comment' => urlencode('Test comment from curl ' . $i),
        'preview' => urlencode(''),
        'entityId' => urlencode($room),
        'parentId' => urlencode($room),
        'level' => urlencode('0'),
        'sentiment' => urlencode(rand(0, 2)),

        'datalet[component]' => $component[$index],
        'datalet[params]' => $params[$index],
        'datalet[fields]' => urlencode(''),
        'datalet[data]' => $data[$index],

        'plugin' => urlencode('agora'),
        'username' => $username[$index],
        'user_url' => urlencode('http://172.16.15.128/user/rended'),
        'user_avatar_src' => urlencode('http://172.16.15.128/ow_userfiles/plugins/base/avatars/avatar_1_1478595591.jpg'),
        'user_avatar_css' => urlencode(''),
        'user_avatar_initial' => urlencode('')
    );

    $fields_string = '';

    foreach($fields as $key=>$value) {
        $fields_string .= $key.'='.$value.'&';
    }
    rtrim($fields_string, '&');

    curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
    $res = curl_exec($ch);
}

curl_close($ch);