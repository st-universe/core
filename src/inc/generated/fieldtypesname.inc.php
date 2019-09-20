<?php
function getFieldName($value): string {
switch ($value) {
case 101:
return _('Wiese');
case 10101:
return _('Wiese (fruchtbar)');
case 10103:
return _('Wiese (großflächig)');
case 111:
return _('Wald');
case 11101:
return _('Wald (fruchtbar)');
case 11103:
return _('Wald (großflächig)');
case 112:
return _('Nadelwald');
case 11201:
return _('Nadelwald (fruchtbar)');
case 11203:
return _('Nadelwald (großflächig)');
case 602:
return _('Ödlandwald');
case 60201:
return _('Ödlandwald (fruchtbar)');
case 60203:
return _('Ödlandwald (großflächig)');
case 121:
return _('Sumpf');
case 12101:
return _('Sumpf (fruchtbar)');
case 122:
return _('Trockengelegter Sumpf');
case 12201:
return _('Trockengelegter Sumpf (fruchtbar)');
case 201:
return _('Wasser');
case 20102:
return _('Wasser (nährstoffreich)');
case 20111:
return _('Wasser (hohe Deuteriumkonzentration)');
case 20132:
return _('Wasser (starke Strömung)');
case 210:
return _('Seichtes Wasser');
case 21011:
return _('Seichtes Wasser (hohe Deuteriumkonzentration)');
case 211:
return _('Korallenriff');
case 21102:
return _('Korallenriff (nährstoffreich)');
case 21111:
return _('Korallenriff (hohe Deuteriumkonzentration)');
case 21132:
return _('Korallenriff (starke Strömung)');
case 212:
return _('abgetragenes Korallenriff');
case 21211:
return _('abgetragenes Korallenriff (hohe Deuteriumkonzentration)');
case 21232:
return _('abgetragenes Korallenriff (starke Strömung)');
case 21202:
return _('abgetragenes Korallenriff (nährstoffreich)');
case 221:
return _('Eisdecke');
case 22111:
return _('Eisdecke (hohe Deuteriumkonzentration)');
case 22102:
return _('Eisdecke (nährstoffreich)');
case 22132:
return _('Eisdecke (starke Strömung)');
case 222:
return _('Eiswasser');
case 22202:
return _('Eiswasser (nährstoffreich)');
case 22211:
return _('Eiswasser (hohe Deuteriumkonzentration)');
case 22232:
return _('Eiswasser (starke Strömung)');
case 231:
return _('Aufgeschüttete Fläche');
case 23111:
return _('Aufgeschüttete Fläche (hohe Deuteriumkonzentration)');
case 232:
return _('Ausgehobenes Wasser');
case 23211:
return _('Ausgehobenes Wasser (hohe Deuteriumkonzentration)');
case 401:
return _('Wüste');
case 40131:
return _('Wüste (starke Sonneneinstrahlung)');
case 402:
return _('Wüste');
case 40231:
return _('Wüste (starke Sonneneinstrahlung)');
case 403:
return _('Sanddüne');
case 40331:
return _('Sanddüne (starke Sonneneinstrahlung)');
case 404:
return _('Sanddüne');
case 40431:
return _('Sanddüne (starke Sonneneinstrahlung)');
case 501:
return _('Eis');
case 50111:
return _('Eis (hohe Deuteriumkonzentration)');
case 511:
return _('Eisformation');
case 51111:
return _('Eisformation (hohe Deuteriumkonzentration)');
case 601:
return _('Ödland');
case 60101:
return _('Ödland (fruchtbar)');
case 60103:
return _('Ödland (großflächig)');
case 60104:
return _('Ödland (unterirdische Höhlen)');
case 611:
return _('Felsformation');
case 61101:
return _('Felsformation (fruchtbar)');
case 61103:
return _('Felsformation (großflächig)');
case 61104:
return _('Felsformation (unterirdische Höhlen)');
case 701:
return _('Berge');
case 70112:
return _('Berge (reiche Erzvorkommen)');
case 70121:
return _('Berge (zusätzliche Dilithiumvorkommen)');
case 70122:
return _('Berge (zusätzliche Tritaniumvorkommen)');
case 702:
return _('Berge');
case 70212:
return _('Berge (reiche Erzvorkommen)');
case 70221:
return _('Berge (zusätzliche Dilithiumvorkommen)');
case 70222:
return _('Berge (zusätzliche Tritaniumvorkommen)');
case 703:
return _('Berge');
case 70312:
return _('Berge (reiche Erzvorkommen)');
case 70321:
return _('Berge (zusätzliche Dilithiumvorkommen)');
case 70322:
return _('Berge (zusätzliche Tritaniumvorkommen)');
case 704:
return _('Berge');
case 70412:
return _('Berge (reiche Erzvorkommen)');
case 70421:
return _('Berge (zusätzliche Dilithiumvorkommen)');
case 70422:
return _('Berge (zusätzliche Tritaniumvorkommen)');
case 705:
return _('Berge');
case 70512:
return _('Berge (reiche Erzvorkommen)');
case 70521:
return _('Berge (zusätzliche Dilithiumvorkommen)');
case 70522:
return _('Berge (zusätzliche Tritaniumvorkommen)');
case 713:
return _('Fels');
case 71304:
return _('Fels (unterirdische Höhlen)');
case 71331:
return _('Fels (starke Sonneneinstrahlung)');
case 715:
return _('Fels');
case 71504:
return _('Fels (unterirdische Höhlen)');
case 725:
return _('Krater');
case 72504:
return _('Krater (unterirdische Höhlen)');
case 731:
return _('großer Krater NW');
case 732:
return _('großer Krater NE');
case 733:
return _('großer Krater SE');
case 734:
return _('großer Krater SW');
case 751:
return _('abgetragene Berge');
case 75112:
return _('abgetragene Berge (reiche Erzvorkommen)');
case 75121:
return _('abgetragene Berge (zusätzliche Dilithiumvorkommen)');
case 75122:
return _('abgetragene Berge (zusätzliche Tritaniumvorkommen)');
case 752:
return _('abgetragene Berge');
case 75212:
return _('abgetragene Berge (reiche Erzvorkommen)');
case 75221:
return _('abgetragene Berge (zusätzliche Dilithiumvorkommen)');
case 75222:
return _('abgetragene Berge (zusätzliche Tritaniumvorkommen)');
case 753:
return _('abgetragene Berge');
case 75312:
return _('abgetragene Berge (reiche Erzvorkommen)');
case 75321:
return _('abgetragene Berge (zusätzliche Dilithiumvorkommen)');
case 75322:
return _('abgetragene Berge (zusätzliche Tritaniumvorkommen)');
case 754:
return _('abgetragene Berge');
case 75412:
return _('abgetragene Berge (reiche Erzvorkommen)');
case 75421:
return _('abgetragene Berge (zusätzliche Dilithiumvorkommen)');
case 75422:
return _('abgetragene Berge (zusätzliche Tritaniumvorkommen)');
case 755:
return _('abgetragene Berge');
case 75512:
return _('abgetragene Berge (reiche Erzvorkommen)');
case 75521:
return _('abgetragene Berge (zusätzliche Dilithiumvorkommen)');
case 75522:
return _('abgetragene Berge (zusätzliche Tritaniumvorkommen)');
case 801:
return _('Untergrund');
case 802:
return _('Untergrund-Fels');
case 811:
return _('freigelegter Untergrund');
case 821:
return _('Untergrundeis');
case 822:
return _('freigelegtes Untergrundeis');
case 831:
return _('Geothermales Bohrloch');
case 841:
return _('Magma');
case 851:
return _('Tiefsee');
case 900:
return _('Weltraum');
case 901:
return _('Planetenring');
case 903:
return _('Planetenring');
case 905:
return _('Planetenring');
case 913:
return _('Planetenring');
case 915:
return _('Planetenring');
case 931:
return _('Planetenring');
case 961:
return _('Planetenring');
case 962:
return _('Planetenring');
case 963:
return _('Planetenring');
case 6101:
return _('Gasriese');
case 6102:
return _('Gasriese');
case 6103:
return _('Gasriese');
case 6104:
return _('Gasriese');
case 6105:
return _('Gasriese');
case 6106:
return _('Gasriese');
case 6107:
return _('Gasriese');
case 6108:
return _('Gasriese');
case 6109:
return _('Gasriese');
case 6110:
return _('Gasriese');
case 6111:
return _('Gasriese');
case 6112:
return _('Gasriese');
case 6113:
return _('Gasriese');
case 6114:
return _('Gasriese');
case 6115:
return _('Gasriese');
case 6116:
return _('Gasriese');
case 6117:
return _('Gasriese');
case 6118:
return _('Gasriese');
case 6119:
return _('Gasriese');
case 6120:
return _('Gasriese');
case 6121:
return _('Gasriese');
case 6122:
return _('Gasriese');
case 6123:
return _('Gasriese');
case 6124:
return _('Gasriese');
case 6125:
return _('Gasriese');
case 6126:
return _('Gasriese');
case 6127:
return _('Gasriese');
case 6128:
return _('Gasriese');
case 6129:
return _('Gasriese');
case 6130:
return _('Gasriese');
case 6131:
return _('Gasriese');
case 6132:
return _('Gasriese');
case 6133:
return _('Gasriese');
case 6134:
return _('Gasriese');
case 6135:
return _('Gasriese');
case 6136:
return _('Gasriese');
case 6137:
return _('Gasriese');
case 6138:
return _('Gasriese');
case 6139:
return _('Gasriese');
case 6140:
return _('Gasriese');
case 6141:
return _('Gasriese');
case 6142:
return _('Gasriese');
case 6143:
return _('Gasriese');
case 6144:
return _('Gasriese');
case 6145:
return _('Gasriese');
case 6146:
return _('Gasriese');
case 6147:
return _('Gasriese');
case 6148:
return _('Gasriese');
case 6149:
return _('Gasriese');
case 6150:
return _('Gasriese');
case 6151:
return _('Gasriese');
case 6152:
return _('Gasriese');
case 6153:
return _('Gasriese');
case 6154:
return _('Gasriese');
case 6155:
return _('Gasriese');
case 6156:
return _('Gasriese');
case 6157:
return _('Gasriese');
case 6158:
return _('Gasriese');
case 6159:
return _('Gasriese');
case 6160:
return _('Gasriese');
case 6201:
return _('Gasriese');
case 6202:
return _('Gasriese');
case 6203:
return _('Gasriese');
case 6204:
return _('Gasriese');
case 6205:
return _('Gasriese');
case 6206:
return _('Gasriese');
case 6207:
return _('Gasriese');
case 6208:
return _('Gasriese');
case 6209:
return _('Gasriese');
case 6210:
return _('Gasriese');
case 6211:
return _('Gasriese');
case 6212:
return _('Gasriese');
case 6213:
return _('Gasriese');
case 6214:
return _('Gasriese');
case 6215:
return _('Gasriese');
case 6216:
return _('Gasriese');
case 6217:
return _('Gasriese');
case 6218:
return _('Gasriese');
case 6219:
return _('Gasriese');
case 6220:
return _('Gasriese');
case 6221:
return _('Gasriese');
case 6222:
return _('Gasriese');
case 6223:
return _('Gasriese');
case 6224:
return _('Gasriese');
case 6225:
return _('Gasriese');
case 6226:
return _('Gasriese');
case 6227:
return _('Gasriese');
case 6228:
return _('Gasriese');
case 6229:
return _('Gasriese');
case 6230:
return _('Gasriese');
case 6231:
return _('Gasriese');
case 6232:
return _('Gasriese');
case 6233:
return _('Gasriese');
case 6234:
return _('Gasriese');
case 6235:
return _('Gasriese');
case 6236:
return _('Gasriese');
case 6237:
return _('Gasriese');
case 6238:
return _('Gasriese');
case 6239:
return _('Gasriese');
case 6240:
return _('Gasriese');
case 6241:
return _('Gasriese');
case 6242:
return _('Gasriese');
case 6243:
return _('Gasriese');
case 6244:
return _('Gasriese');
case 6245:
return _('Gasriese');
case 6246:
return _('Gasriese');
case 6247:
return _('Gasriese');
case 6248:
return _('Gasriese');
case 6249:
return _('Gasriese');
case 6250:
return _('Gasriese');
case 6251:
return _('Gasriese');
case 6252:
return _('Gasriese');
case 6253:
return _('Gasriese');
case 6254:
return _('Gasriese');
case 6255:
return _('Gasriese');
case 6256:
return _('Gasriese');
case 6257:
return _('Gasriese');
case 6258:
return _('Gasriese');
case 6259:
return _('Gasriese');
case 6260:
return _('Gasriese');
case 6301:
return _('Gasriese');
case 6302:
return _('Gasriese');
case 6303:
return _('Gasriese');
case 6304:
return _('Gasriese');
case 6305:
return _('Gasriese');
case 6306:
return _('Gasriese');
case 6307:
return _('Gasriese');
case 6308:
return _('Gasriese');
case 6309:
return _('Gasriese');
case 6310:
return _('Gasriese');
case 6311:
return _('Gasriese');
case 6312:
return _('Gasriese');
case 6313:
return _('Gasriese');
case 6314:
return _('Gasriese');
case 6315:
return _('Gasriese');
case 6316:
return _('Gasriese');
case 6317:
return _('Gasriese');
case 6318:
return _('Gasriese');
case 6319:
return _('Gasriese');
case 6320:
return _('Gasriese');
case 6321:
return _('Gasriese');
case 6322:
return _('Gasriese');
case 6323:
return _('Gasriese');
case 6324:
return _('Gasriese');
case 6325:
return _('Gasriese');
case 6326:
return _('Gasriese');
case 6327:
return _('Gasriese');
case 6328:
return _('Gasriese');
case 6329:
return _('Gasriese');
case 6330:
return _('Gasriese');
case 6331:
return _('Gasriese');
case 6332:
return _('Gasriese');
case 6333:
return _('Gasriese');
case 6334:
return _('Gasriese');
case 6335:
return _('Gasriese');
case 6336:
return _('Gasriese');
case 6337:
return _('Gasriese');
case 6338:
return _('Gasriese');
case 6339:
return _('Gasriese');
case 6340:
return _('Gasriese');
case 6341:
return _('Gasriese');
case 6342:
return _('Gasriese');
case 6343:
return _('Gasriese');
case 6344:
return _('Gasriese');
case 6345:
return _('Gasriese');
case 6346:
return _('Gasriese');
case 6347:
return _('Gasriese');
case 6348:
return _('Gasriese');
case 6349:
return _('Gasriese');
case 6350:
return _('Gasriese');
case 6351:
return _('Gasriese');
case 6352:
return _('Gasriese');
case 6353:
return _('Gasriese');
case 6354:
return _('Gasriese');
case 6355:
return _('Gasriese');
case 6356:
return _('Gasriese');
case 6357:
return _('Gasriese');
case 6358:
return _('Gasriese');
case 6359:
return _('Gasriese');
case 6360:
return _('Gasriese');
    default:
        return _('Unbekannt');
}
}
?>
