DROP TABLE IF EXISTS `startpoints`;

CREATE TABLE `startpoints`
(
id MEDIUMINT NOT NULL AUTO_INCREMENT,
divid CHAR(10) NULL,
title VARCHAR(50) NULL,
lat NUMERIC(11,8) NOT NULL,
lon NUMERIC(11,8) NOT NULL,
grouping CHAR(10) NOT NULL,
startcentre CHAR(1) NOT NULL,
description VARCHAR(2048) NULL,
PRIMARY KEY (id)
);

INSERT INTO `startpoints`
(
divid, title, lat, lon, grouping, startcentre, description
)
VALUES
(
'mfount','The Fountain', 51.46593500, -0.97886600, 'RDG-GEN', 'S', 
'This is on Thames Side Promenade between the Crowne Plaza Hotel and Reading Rowing Club boathouse, upstream from the south end of Caversham bridge Free parking available OS Grid Ref: Sheet 175 / 710746 Nearest railway station is Reading.'
),
(
'mearley', 'Earley', 51.42598300, -0.93305200, 'RDG-GEN', 'S', 
'Rides sometimes start here, particularly if they are heading south. We meet at the cycle racks outside the entrance to the Earley Retreat Pub. This is in the car park for the library, Church and the Earley Retreat PH, OS Grid Ref: Sheet 175 / 743702'
),
(
'mdinton','Dinton Pastures', 51.44015600, -0.87253400, 'RDG-GEN', 'S',
'Dinton Pastures Country Park is just off the B3030 north of Winnersh, RG10 0TH. We meet in the main car park outside the Dragonfly Café. There is a charge for car parking (£6.00 for 4 hours+ (Jan 2016)) Nearby railway station (Reading/Waterloo line) is Winnersh OS Grid Ref: Sheet 175 / 784717'
),
(
'mtheale', 'Theale', 51.43904400, -1.07125500, 'RDG-GEN', 'S',
'We meet in the car park at east end of Theale High Street, RG7 5AL. It is easily reached over M4 footbridge near junction 12. Car park charges apply (£0.90 for 2 hours+ (Nov 2014)) OS Grid Ref: Sheet 175 / 646714'
),
(
'mprospect', 'Prospect Park', 51.450141, -1.0056, 'RDG-GEN', 'S',
'We meet in the car park in Prospect Park. The entrance is from Liebenrood Road which runs between Bath Road and Tilehurst Road in West Reading. OS Grid Ref: 175 / 692728'
),
(
'mreading', 'Reading Town Centre', 51.454439, -0.974059, 'RDG-GEN', 'C',
'No description'
)
;

