<?php
    include 'framework.php';
    
    echo "
<h1>Om $prj_name</h1>
   
$prj_name er navnet på internsiden for medlemmer av Oslo Symfoniorkester. 
Her skal du som medlem finne oppdatert informasjon om alle prosjekter du er med på. 
All informasjonen er tilpasset deg som individuell bruker. 
Har du f.eks. permisjon fra et prosjekt vil dette prosjektet ikke fremgå i din spilleplan.
<p>
Hvis du er usikker på hva noe betyr, eksempelvis fargekodingen, 
kan du flytte musepekeren over og en forklarende tekst vil komme frem.
<p>
<i>[I høyre hjørne velger]</i> du for hvilket semester du ønsker å se informasjon, 
om det er for høstsemesteret inneværende år, eller om du det er planene for neste semesters prosjekter du ønsker å se. 

<h2>Innlogging</h2>
Innlogging til siden er initialene i navnet i navnet ditt i små bokstaver. 
Passord for første innlogging er fødselsåret ditt. 
Styret oppfordrer alle medlemmene til å lage et nytt passord etter første innlogging. 
Dette gjør du Mine personopplysninger under Min side. 

<h2>Å finne frem i Stretto - 
Hvordan fungerer internsidene for deg som orkestermedlem?</h2>
I menyen øverst til venstre navigerer du rundt på siden. 
Menyen er delt i 4 for deg som er innlogget som orkestermedlem. 
Under disse hovedknaggene finner du følgende informasjon:

<h3>Mine sider</h3
<h4>Mine prosjekter: </h4>
Dette er oversikten over alle orkesterets prosjekter og hvilke av disse du skal være med på. 
Kolonnen Tutti viser om prosjektet er et tuttiprosjekt for alle eller om det er et prosjekt med redusert besetning. 
<p>
Under status ser du status for uttaket, om styret har vedtatt hvem som skal være med etc. 

<h4>Min spilleplan:</h4>
Dette er din individuelle spilleplan basert på de prosjekter du skal være med på. 

<h4>Min regi:</h4>
	Oversikt over hvem som er regikomité for prosjektene. Under status ser du hvilket
        prosjekt du er valgt ut å være regikomité for.
<p>
	Klikker du deg inn på prosjektet ser du hva du må gjøre og hva som forventes av deg
        når du sitter i regikomiteen. Du får også oversikt og kontaktinfo til regikomitéen, samt
        oversikt over møtetider og -sted.

<h4>Mine personopplysninger:</h4>
	Oversikt over dine personopplysninger. Disse kan du oppdatere forløpende. 
	Her bestemmer du brukernavn og passord for din bruker. 

<h3>Admin</h3>
<h4>Medlemsliste:<h4>
	Oversikt og kontaktinformasjon til orkesterets medlemmer. 
	Ønsker du å skrive ut listen trykker du på pdf-ikonet til venstre over listen. 

<h4>Spilleplan:</h4>
Dette er din individuelle spilleplan for de prosjektene du skal være med på.
Ønsker du å skrive ut planen trykker du på pdf-ikonet til venstre over listen.

<h4>Dokumenter:</h4>
Her finner du dokumenter som omhandler orkesteret. Eksempelvis vedtekter, 
sakspapirer generalforsamling, informasjon til nye medlemmer, instruks for musikalsk råd etc.

<h4>Om Stretto:</h4>
Oversikt over hvordan internsiden Stretto kan brukes og navigeres i. 

<h3>Prosjekter</h3>
Her finner du oversikt og informasjon til alle orkesterets prosjekter. 
Klikker du deg inn på prosjektet ser får du detaljert informasjon om det enkelte prosjekt.
Inn under hvert prosjekt finner du:

<h4>Prosjektinfo:</h4>
Her finner du generell informasjon om prosjektet, oversikt over repertoar, 
prøvetid og -sted og hvilke musikere som er med på dette prosjektet. 
Ønsker du å skrive ut listen trykker du på pdf-ikonet til venstre over listen. 

<h4>Beskjeder</h4>
Her finner du beskjeder knyttet til det enkelte prosjekt. 
Disse beskjedene finnes også samlet under Hva skjer?-fanen i menyen, 
hvor alle beskjeder ligger, uavhengig av prosjekt.

<h4>Gruppeoppsett</h4>
Oversikt over gruppeoppsett for din instrumentgruppe. 
Ønsker du å skrive ut oppsettet trykker du på pdf-ikonet til venstre over listen. 

<h4>Musikere</h4>
Oversikt med kontaktinformasjon til musikerne som deltar i prosjektet. 
Ønsker du å skrive ut oppsettet trykker du på pdf-ikonet til venstre over listen. 

<h4>Regikomité</h4>
Oversikt med kontaktinformasjon til regikomité for prosjektet.
	Av oversikten fremgår hva regikomiteen må gjøre samt oversikt over møtetider og -sted.

<h4>Påmelding</h4>
Her melder du deg på eller av det enkelte prosjekt. 
Siden inneholder også oversikt over prøver og prøvetider for prosjektet 
slik at du kan se dette mens du krysser av påmeldingsskjema.

<h4>Konsertreklame</h4>
Adresselink til denne siden kan sendes til venner og bekjente som informasjon og reklame for konserten. 

<h4>Hva skjer?</h4>
Oversikt over viktige beskjeder som hjelder dette prosjektet. 
Sjekker du denne siden er du oppdatert på siste hendelser, beskjeder og planer.

";
    
if ($access->auth(AUTH::BOARD_RO))
    echo "
<h1>Styrefunksjoner</h1>
Som medlem av styret eller MR or du ansvarlig for å ajourholde informasjon
i $prj_name. Avhengig av hvilkes styrefunksjon du har,
er du ansvarlig for følgende informasjon:
";

if ($access->auth(AUTH::BOARD_RO))
    echo "
<h2>Regi</h2>
Som regissør, er du ansvarlig for:
<ul>
  <li>å holde ajour alle regiplaner for alle kommende prosjekter.</li>
  <li>Legge inn informasjon om prøvelokaler og konsertlokaler</li>
  <li>å allokere ressurser til regikomitéer og informere disse forventet ansvar</li>
  <li>å holde orden på OSOs notearkiv (sammen med kunstnerisk leder)</li>
</ul>
";

if ($access->auth(AUTH::BOARD_RO))
    echo "
<h2>Prosjekter</h2>
Kunstnerisk leder er ansvarlig for:
<ul>
  <li>å definere opp nye prosjekter som skal vureders av MR og styret og etterhvert
      gjøres synlige for resten an medlemmene i orkesteret</li>
  <li>legge inn prøveplaner for de enkelte prosjektene</li>
  <li>legge inn repertoar for prosjektet</li>
  <li>legge inn konsertdatoer i konsertkalenderen.</li>
  <li>legge inn aktuell besetning i ressursoversikten pr prosjekt</li>
  <li>legge inn MRs innstilling i ressursoversikten for påmeldingsprosjekter</li>
</ul>
";

if ($access->auth(AUTH::BOARD_RO))
    echo "
<h2>PR</h2>
Er du PR-sekretær, er du ansvarlig for:
<ul>
  <li>å legge inn detaljinformasjon om orkesterets konserter i konsertkalenderen.</li>
</ul>
";

if ($access->auth(AUTH::ABS_RO))
    echo "
<h2>Fravær</h2>
Er du gruppeleder, er du ansvarlig for:
<ul>
  <li>å registrere fravær på prøvene.</li>
  <li>legge ut øvingsnoter</li>
  <li>Definere gruppeoppsett (stryk)</li>
</ul>
";

if ($access->auth(AUTH::BOARD_RO))
    echo "
<h2>PR</h2>
Er du sekretær, er du ansvarlig for:
<ul>
  <li>å holde medlemslisten ajour</li>
  <li>å gi medlemmene riktig tilgang (autorisasjon)</li>
  <li>å holde orden på listen med grupper</li>
  <li>å registrere og holde orden på permisjoner og påmeldinger</li>
  <li>at ressursene som skal være med på de ulike prosjektene er korrekt</li>
  <li>distribuere viktig informasjon gjennem fellesmail og \"Hva skjer...?\"</li>
  <li>at viktige dokumenter som vedtekter, generalforsamplingspapirer etc. er tilgjengeliget</li>
</ul>
";

if ($access->auth(AUTH::BOARD_RO))
    echo "
<h2>PR</h2>
Er du kasserer, er du ansvarlig for:
<ul>
  <li>registrere innbetalinger som kontigenter o.l.</li>
</ul>
";

if ($access->auth(AUTH::BOARD_RO))
    echo "
<h2>Kvalitetssikring</h2>
Er du styreleder, er du ansvarlig for:
<ul>
  <li>generelt å kontrollere at informasjonen som ligger er korrekt til enhver tid</li>
</ul>
";

