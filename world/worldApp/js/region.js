//Create the viewer
//var viewer = new Cesium.Viewer('cesiumContainer', {baseLayerPicker : false});

//var viewer = new Cesium.Viewer('cesiumContainer');


Cesium.viewerEntityMixin(viewer);

//Example 1: Load a GeoJSON or TopoJSON file with default settings.
Sandcastle.addDefaultToolbarButton('Basic loading', function() {
  viewer.dataSources.add(Cesium.GeoJsonDataSource.fromUrl('../../Apps/SampleData/russiaRed.json'));
//  viewer.dataSources.add(Cesium.GeoJsonDataSource.fromUrl('../../Apps/SampleData/russia.json'));
});

//Example 2: Apply custom graphics to a GeoJSON or TopoJSON file 
//based on the metadata contained in the file.
Sandcastle.addToolbarButton('Custom Graphics', function() {
  //Seed the random number generator for repeatable results.
  Cesium.Math.setRandomNumberSeed(0);

  //Create a new GeoJSON data source and add it to the list.
  var dataSource = new Cesium.GeoJsonDataSource();
  viewer.dataSources.add(dataSource);

  //Load the document into the data source and then set custom graphics
  dataSource.loadUrl('../../Apps/SampleData/russiaRed.json').then(function() {
//  dataSource.loadUrl('../../Apps/SampleData/russia.json').then(function() {
    //Get the array of entities
    var entities = dataSource.entities.entities;

    var colorHash = {};
    for (var i = 0; i < entities.length; i++) {
      //For each entity, create a random color based on the state name.
      //Some states have multiple entities, so we store the color in a
      //hash so that we use the same color for the entire state.
      var entity = entities[i];
      var name = entity.name;
      var color = colorHash[name];

      console.log('entities: ' + entities);
      console.log('name: ' + name);
      console.log('color: ' + color);
      if (!color) {
        color = Cesium.Color.fromRandom({
          alpha : 1.0
        });
        colorHash[name] = color;
      }
      console.log('color: ' + color);

      //Set the polygon material to our random color.
      entity.polygon.material = Cesium.ColorMaterialProperty.fromColor(color);

      //Extrude the polygon based on the state's population.  Each entity
      //stores the properties for the GeoJSON feature it was created from
      //Since the population is a huge number, we divide by 50.
      entity.polygon.extrudedHeight = new Cesium.ConstantProperty(entity.properties.Population / 50.0);
    }
  });
});

//Reset the scene when switching demos.
Sandcastle.reset = function() {
  viewer.dataSources.removeAll();

  //Set the camera to a US centered tilted view.
  var camera = viewer.scene.camera;
//  55.749792,37.632495 - sample moscow
  camera.lookAt(Cesium.Cartesian3.fromDegrees(90, 45, 15000000),
        Cesium.Cartesian3.fromDegrees(90, 50, 0), Cesium.Cartesian3.UNIT_Z);
  console.log('Cesium.Cartesian3.UNIT_Z: ');
  console.log(Cesium.Cartesian3.UNIT_Z);
};
