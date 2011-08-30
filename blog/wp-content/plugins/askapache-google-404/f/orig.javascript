function a16g(B) { var b = document.getElementById(B); return (typeof b != 'undefined' && typeof b === "object") ? b: false;}


google.load("search", "1");

function OnLoad() {new cse();}
function cse() {
	
  // the GoogleContainer
  var GoogleContainer = a16g("g404ajax");
  this.GoogleContainer = GoogleContainer;
  
  // create the divs
  var sFormDiv = a16g("sFormDiv");
  var gDiv = a16g("gDiv");
  var lDiv = a16g("lDiv");
  var rDiv = a16g("rDiv");
  
  this.sFormDiv = sFormDiv;
  this.gDiv = gDiv;
  this.lDiv = lDiv;
  this.rDiv = rDiv;

  
   // create the search form in sFormDiv and add event handlers
  this.sForm = new GSearchForm(true, sFormDiv);
  this.sForm.setOnSubmitCallback(this, cse.prototype.onSubmit);
  this.sForm.setOnClearCallback(this, cse.prototype.onClear);

  
  // the search controls
  this.gCT = new GSearchControl();
  this.lCT = new GSearchControl();
  this.rCT = new GSearchControl();
  
    // set the results size
  this.lCT.setResultSetSize(GSearch.SMALL_RESULTSET);
  this.rCT.setResultSetSize(GSearch.LARGE_RESULTSET);
  this.gCT.setResultSetSize(GSearch.SMALL_RESULTSET);

  // set the default link target
  this.lCT.setLinkTarget(GSearch.LINK_TARGET_SELF);
  this.rCT.setLinkTarget(GSearch.LINK_TARGET_SELF);
  this.gCT.setLinkTarget(GSearch.LINK_TARGET_SELF);




  var gSearchGoogleGuess = new GwebSearch();
  var gSearchVideo = new GvideoSearch();
  var gSearchImage = new GimageSearch();
  var gSearchSite = new GwebSearch();
  var gSearchBlog = new GblogSearch();
  var gSearchWeb = new GwebSearch();
  var gSearchNews = new GnewsSearch();
  var gSearchCSE = new GwebSearch();
  var gSearchLocal = new GlocalSearch();
  
  


	//EXPAND_MODE_CLOSED
	//EXPAND_MODE_OPEN
	//EXPAND_MODE_PARTIAL
  var sOPT = new GsearcherOptions();
  sOPT.setExpandMode(GSearchControl.EXPAND_MODE_OPEN);

  var drawOptions = new GdrawOptions();
  drawOptions.setSearchFormRoot(sFormDiv);
  drawOptions.setDrawMode(GSearchControl.DRAW_MODE_OPEN);
  
  
  
  /* Googles Best Guess Setup
  */
  gSearchGoogleGuess.setUserDefinedLabel("Googles Best Guess");
  gSearchGoogleGuess.setSiteRestriction(aa_MYSITE);
  gSearchGoogleGuess.setQueryAddition(aa_BGLABEL);
  this.gCT.addSearcher(gSearchGoogleGuess, sOPT);
  this.gCT.draw(a16g("gDiv"), drawOptions);


  drawOptions = new GdrawOptions();
  sOPT = new GsearcherOptions();
  drawOptions.setSearchFormRoot(sFormDiv);
  drawOptions.setDrawMode(GSearchControl.DRAW_MODE_LINEAR);
  sOPT.setExpandMode(GSearchControl.EXPAND_MODE_OPEN);
  

  gSearchVideo.setQueryAddition(aa_LABEL);
  
   // Add the searcher to the SearchControl
  this.lCT.addSearcher(gSearchVideo, sOPT);
  sOPT = new GsearcherOptions();
  sOPT.setExpandMode(GSearchControl.EXPAND_MODE_OPEN);

  gSearchImage.setRestriction(GSearch.RESTRICT_SAFESEARCH, GSearch.SAFESEARCH_OFF);
  gSearchImage.setQueryAddition(aa_LABEL);
  
   // Add the searcher to the SearchControl
  this.lCT.addSearcher(gSearchImage, sOPT);
  this.lCT.draw(a16g("lDiv"), drawOptions);



  gSearchSite.setUserDefinedLabel(aa_LABEL);
  gSearchSite.setSiteRestriction(aa_MYSITE);
  gSearchSite.setQueryAddition(aa_LABEL);
  gSearchSite.setRestriction(GSearch.RESTRICT_SAFESEARCH, GSearch.SAFESEARCH_OFF);

  gSearchBlog.setQueryAddition(aa_LABEL);
  gSearchBlog.setResultOrder(GSearch.ORDER_BY_DATE);

  gSearchWeb.setQueryAddition(aa_LABEL);
  gSearchWeb.setRestriction(GSearch.RESTRICT_SAFESEARCH, GSearch.SAFESEARCH_OFF);

  gSearchCSE.setQueryAddition("askapache");
  gSearchCSE.setUserDefinedLabel("CSE");
  gSearchCSE.setRestriction(GSearch.RESTRICT_SAFESEARCH, GSearch.SAFESEARCH_OFF);
  gSearchCSE.setSiteRestriction("002660089121042511758:kk7rwc2gx0i", null);


  // Add the searcher to the SearchControl
  this.rCT.addSearcher(gSearchSite);
  this.rCT.addSearcher(gSearchBlog);
  this.rCT.addSearcher(gSearchWeb);
  this.rCT.addSearcher(gSearchNews);
  this.rCT.addSearcher(gSearchCSE);
  this.rCT.addSearcher(gSearchLocal);

  drawOptions.setDrawMode(GSearchControl.DRAW_MODE_TABBED);
  this.rCT.draw(a16g("rDiv"), drawOptions);


  // execute the search, firing the other 3 searches as well
  this.sForm.execute(aa_XX);
};



cse.prototype.onSubmit = function (form) {
  var q = form.input.value;
  if (q && q != "") {
    this.gCT.execute(q);
    this.lCT.execute(q);
    this.rCT.execute(q);
  };
  return false;};
cse.prototype.onClear = function (form) {
  form.input.value = "";
  this.gCT.clearAllResults();
  this.lCT.clearAllResults();
  this.rCT.clearAllResults();
  return false;};



google.setOnLoadCallback(OnLoad);