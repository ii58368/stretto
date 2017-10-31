<?php
    include 'framework.php';
    
    echo "
<h1>Velkomment til $prj_name</h1>
Her finner du oppdatert informasjon om all orkesteretes virksomhet. 
Følg med, og du vil alltid være oppdatert med alle prosjekter i OSO.

";
    
if ($access->auth(AUTH::MYPRJ))
    echo "
<h2>Medlem</h2>
Som medlem har du full oversikt over alle orkesterets medlemmer og spilleplaner.
Trenger du søke om permisjon eller melde deg på til prosjekter med redusert besetning,
kan du gjøre dette fra mine sider.
Er du med i regikomitéen, finner du alle regiplaner her.
";

if ($access->auth(AUTH::BOARD_RO))
    echo "
<h2>Regi</h2>
Er du regissør, er du ansvarlig for:
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

