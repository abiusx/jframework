<?php
/*
 * Logout the user or let him log out whence session is expired!

 * Remove old messages form the div. page gets heavy pretty soon
 *  */
		/**
      * jChat iFrame Interface
      *
      * Version 1.1.9
      * tested on FF, IE, Camino, Safari worked well
      */
     class jChat_Interface_Iframe extends BasePluginClass 
     {
          function Present($Width=null,$Height=null,$Channel=0,$Title='jChat')
          {
              $Width-=2;
              $Height-=2;
              $Button_Width=45;
              $Send_Width=$Width-$Button_Width-10;
              $Send_Height=20;
              $Status_Height=22;
              $Messages_Width=$Width;
              $Messages_Height=$Height-$Send_Height-$Status_Height;
              
              ?>
<script>
var jChat={
	serviceRootUrl:"<?php echo SiteRoot."/service.jchat." ?>"
	,channel:"<?php echo $Channel?>"
	,lastID:"0"
	,nickname:null
	,interval:2000
	,asyncHandler:null
	,underProcess:null
	,autoLogin:null
	,lastMessage:null
	,autoReceive:null
	,callService:function(title,params,callback)
	{
    	$.get(jChat.serviceRootUrl+title+"?output=json",
    			params
    			,callback
    		);			

	}
	,receive:function()
	{
		if (jChat.underProcess) return;
		if (jChat.nickname==null)
			return;
		jChat.processing();
		jChat.callService("receive",
				{"LastID":jChat.lastID,"Channel":jChat.channel}
				,jChat.receiveCallback
			);			
	}
	,receiveCallback:function(data)
	{
		jChat.processDone();
		eval("x="+data);
		if (x.Err=="1")
		{
			//no new message
		}
		else if (x.Err=="2")
		{
			//invalid lastID
		}
		else
		{
			if (jChat.lastID==null || jChat.lastID==undefined)
				jChat.lastID="0";
			var m=$(".jChatMessages");
			var out="";
	
			for(i=0;i<x.length;++i)
			{
				msg=x[i];
				if (msg.Nickname==jChat.nickname)
				{
					color="<span style='color:blue;'> ";
					color2="</span>";
				}
				else
					color=color2="";
				out+=color+msg.Nickname+color2+": "+msg.Message+"<br/>";
				if (msg.ID>jChat.lastID)
					jChat.lastID=msg.ID;
			}
			m.html(m.html()+out);
			var div=document.getElementById("jChatMessages");
			
			$(".jChatMessages").attr("scrollTop", $(".jChatMessages").attr("scrollHeight"));
			
		}
		if (jChat.autoReceive)
			jChat.cycle();
	}
	,send:function()
	{
		if (jChat.nickname==null)
		{
			alert("ابتدا وارد سیستم شوید سپس پیام بفرستید.");
			return;
		}
		var msg=$(".jChatSendField").val();
		$(".jChatSendField").val("");
		if (jChat.lastMessage==msg)
		{
			alert("لطفا پیام تکراری نفرستید");
			return;	
		}
		jChat.processing();
		jChat.lastMessage=msg;
		jChat.callService("send",{"Channel":jChat.channel,"Message":msg},
					jChat.sendCallback);
		return false; //stop submission
	}
	,sendCallback:function(data)
	{
		jChat.processDone();
		eval("x="+data);
		if (x.Err=="1")
		{
			//not logged in!
			alert("به دلیل اینکه وارد سیستم نشده اید، پیام شما ارسال نشد.");
			jChat.whomai();
		}
		else if (x.Err=="2")
		{
			//dont send empty message!
			alert("پیام خالی نفرستید!");
		}
		else
		{
			//success! x is messageID
		}
	}
	,whoami:function()
	{
		jChat.processing();
		jChat.callService("whoami",
				{"Channel":jChat.channel},jChat.whoamiCallback);
	}
	,whoamiCallback:function(data)
	{
		jChat.processDone();
		eval("x="+data);
		if (x.Err=="1")
		{
										//("ورود به سیستم انجام نگرفته است.");
		}
		else
		{
			jChat.user=x.User;
			jChat.nickname=x.User.Nickname;
			jChat.lastID=x.LastID; //remove this to get previous messages
			jChat.ready();
			
		}
	}
	,join:function()
	{
	var myNickname="";
 	myNickname=$(".jChatSendField").val();
	if (myNickname=="" || myNickname==null || myNickname==undefined)
	{
		alert("لطفا یک نام مستعار برای خود برگزینید!");
		return;
	}
	jChat.processing();
		jChat.callService("join",
				{"Channel":jChat.channel,"Nickname":myNickname}
			,jChat.joinCallback);
	}
	,joinCallback:function(data)
	{
		jChat.processDone();
		eval("x="+data);
		if (x.Err=="1")
		{
			//Already in room
			alert("شما همکنون وارد گفتگو شده اید!");
			jChat.whoami();
		}
		else if (x.Err=="2")
		{
			//Nickname exists
			alert("نام مستعار شما توسط فرد دیگری استفاده شده است، لطفا نام دیگری برگزینید.");
		}
		else if (x.Err=="3")
		{
			//Nickname too short
			alert("نام مستعار بسیار کوتاهی انتخاب کرده اید، لطفا مجددا سعی کنید.");
		}
		else
		{
			jChat.whoami();
		}				
	}
	,leave:function()
	{
		jChat.processing();
		jChat.callService("leave",{"Channel":jChat.channel},jChat.leaveCallback);
		
	}
	,leaveCallback:function(data)
	{
		jChat.processDone();
		eval ("x="+data);
		if (x.Err=="1")
			alert("شما وارد گفتگو نشده اید، نمی توانید خارج شوید!");
		else
		{
			jChat.nickname=null;
			jChat.lastID=0;
			jChat.stop();
			jChat.waiting();
		}
	}
	,start:function()
	{
		jChat.cycle();
		
	}
	,cycle:function()
	{	
		jChat.autoReceive=true;
		jChat.asyncHandler=setTimeout(jChat.receive,jChat.interval);
	}
	,stop:function()
	{
		clearTimeout(jChat.asyncHandler);
	}
	,processing:function()
	{
		jChat.underProcess=true;
		$(".jChatAnimator").show();
		$(".jChatSendButton").attr("disabled","disabled");
		$(".jChatStatus").attr("diasbled","disabled");
	}
	,processDone:function()
	{
		jChat.underProcess=null;
		$(".jChatSendButton").attr("disabled","");
		$(".jChatAnimator").fadeOut();
	}
	,getList:function()
	{
		jChat.processing();
		jChat.callService("list",{"Channel":jChat.channel},jChat.getListCallback);
		
		
	}
	,getListCallback:function(data)
	{
		jChat.processDone();
		eval("x="+data);
		if (x.Err=="1")
		{
			//room empty!
		}
		else
		{
			var m=$(".jChatList");
			var out="";
			for(i=0;i<x.length;++i)
			{
				msg=x[i];
				out+=msg.Nickname+" ("+msg.JoinTimestamp+")<br/>";
			}
			m.html(out);
		}
	}
	,showList:function()
	{
		$(".jChatShowListLink,.jChatShowChatLink").hide();
		jChat.getList();
		$(".jChatMessages").slideUp("slow",function(){
		$(".jChatList").slideDown("normal");
				});
		$(".jChatShowChatLink").show();
		
	}
	,showChat:function()
	{
		$(".jChatShowListLink,.jChatShowChatLink").hide();
		$(".jChatList").slideUp("slow",function(){
		$(".jChatMessages").slideDown("normal");
				});
		$(".jChatShowListLink").show();
		
	}
	,ready:function()
	{
		$(".jChatMessages").html("<span style='color:green;'>"+jChat.nickname+
				"، شما همکنون وارد اتاق "+jChat.channel+" شدید. امیدواریم از گفتگوی خود لذت ببرید.</span><hr/>");
		jChat.start();
		jChat.chatting();
	}
	,chatting:function() //for when logged in
	{
		$(".jChatSendButton").val("ارسال");
		$(".jChatSendField").val("");
		jChat.state="ready";
	}
	,waiting:function() //for when not logged in
	{
		$(".jChatMessages").text("");
		$(".jChatSendButton").val("ورود");
		$(".jChatSendField").val("نام مستعار من");
		jChat.state="waiting";
	}
	,button:function()
	{
		if (jChat.state=="waiting")
		{
			jChat.join();
		}
		else
		{
			jChat.send();
		}
	}
	,selectAll:function()
	{
		$(".jChatSendField").select();
	}
};
$(document).ready(function(){
    jChat.waiting();
    jChat.whoami();
});

</script>
              
<div class="jChatWindow" dir="rtl">

<div class="jChatStatus">
    <a href="javascript:jChat.leave();" class="jChatLeaveLink" 
    	title=""
    	>
    	خروج
    </a>
    <a href="javascript:jChat.showList();" class="jChatShowListLink" 
    	title=""
    	>
    لیست 	
    </a>
    <a href="javascript:jChat.showChat();" class="jChatShowChatLink" 
    	title="" style="display:none;"
    	>
    	گفتگو
    </a>
	<?php echo $Title ?>
</div> <!--  jChatLiks -->
<img class="jChatAnimator" src="img.loading.gif" title="درحال کار کردن..." />
<br/>
<form class="jChatForm" onsubmit="jChat.button(); return false;">
<div class="jChatList">

</div> <!--  jChatList -->
<div class="jChatMessages">
</div> <!--  jChatMessages -->

<input type="text" class="jChatSendField" maxlength="256" onclick="jChat.selectAll();"/>
<input type="submit"   class="jChatSendButton" value="ورود" onmouseover="$('.jChatSendButton').css('background-color','gray');" onmouseout="$('.jChatSendButton').css('background-color','white');"/>
</form>
</div> <!-- jChatWindow -->
	<style>
	.jChatForm {
		font-family:"Tahoma";
			font-size:small;
	
	}
	.jChatList {
		font-family: "Tahoma";
		overflow: auto;
		padding:4px;
		background-color:green;
		border: 1px inset;
		width:<?php echo $Messages_Width-10?>px;
		height:<?php echo $Messages_Height-10;?>px;
		display:none;
	}
	.jChatWindow {
		
		border:1px solid;
		background-color:white;
		font-family:"Tahoma";
		margin:0px;
		padding:0px;
		width: <?php echo $Width?>px;
		height: <?php echo $Height?>px;
	}
	.jChatMessages {
		font-family:"Tahoma";
		font-size:smaller;
		padding:1px;
		overflow: auto;
		border: 0px inset;
		border-top:1px solid;
		width: <?php echo $Messages_Width-4?>px;
		height: <?php echo $Messages_Height-4?>px;
		
	}
	.jChatSendField {
		font-family:"Tahoma";
		font-size:small;
		border:1px solid;
		float:right;
		width: <?php echo $Send_Width?>px;
		height: <?php echo $Send_Height+1?>px;
		padding:0px;
		margin:0px;
		outline:none;
		vertical-align: top;
	}
	.jChatSendButton {
		font-size:small;
		font-family:"Tahoma";
		float:left;
		border: 0px solid;
		width: <?php echo $Button_Width?>px;
		height: <?php  echo $Send_Height?>px;
		background-color:white;
		cursor:pointer;
		
	}
	.jChatStatus {
		font-family:"Tahoma";
		font-size:10px;
		padding-right:2px;
		padding-left:5px;
		float:right;
	}
	.jChatStatus * {
		font-size:small;
		font-family:"Tahoma";
		margin-left:5px;
	}
	.jChatAnimator {
		font-family:"Tahoma";
		font-size:small;
		padding:2px;
		float: left;
		width:16px;
		height:16px;
		display:none;
	}
</style>

	
              <?php    
          }
     }
?>