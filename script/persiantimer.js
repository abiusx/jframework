

var PersianTimer = {
		year:0
		,month:0
		,day:0
		,hour:0
		,minute:0
		,second:0
		,target:null
		,state:"pause"
		,intervalHandler:null
		,set:function(y,m,d,h,mi,s)
		{
			PersianTimer.year=y;
			PersianTimer.month=m;
			PersianTimer.day=d;
			PersianTimer.hour=h;
			PersianTimer.minute=mi;
			PersianTimer.second=s;
		}
		,assign:function(targetID)
		{
			PersianTimer.target=document.getElementById(targetID);
		}
		,cycle:function()
		{
			if (PersianTimer.state=='play')
			{
				//setTimeout(PersianTimer.cycle,1000);
				PersianTimer.second++;
				if (PersianTimer.second==60)
				{
					PersianTimer.second=0;
					PersianTimer.minute++;
				}
				if (PersianTimer.minute==60)
				{
					PersianTimer.minute=0;
					PersianTimer.hour++;
				}
				if (PersianTimer.hour==24)
				{
					PersianTimer.hour=0;
					PersianTimer.day++;
				}				
			PersianTimer.target.innerHTML=PersianTimer.Year()+
			"/"+PersianTimer.Month()+"/"+PersianTimer.Day()+" "+
			PersianTimer.Hour()+":"+PersianTimer.Minute()+":"+PersianTimer.Second();

			
			}
		}
		,pause:function()
		{
			PersianTimer.state="pause";
		}
		,play:function()
		{
			PersianTimer.state="play";
			//PersianTimer.cycle();
			PersianTimer.cycle();
			
			PersianTimer.intervalHandler=setInterval(PersianTimer.cycle,1000);
		}
		,fixedWidth:function(number)
		{
			var str=number+"";
			if (str.length==1) str="0"+str;
			return str;
		}
		,Year:function()
		{
			return PersianTimer.fixedWidth(PersianTimer.year);
		}
		,Month:function()
		{
			return PersianTimer.fixedWidth(PersianTimer.month);
		}
		,Day:function()
		{
			return PersianTimer.fixedWidth(PersianTimer.day);
		}
		,Hour:function()
		{
			return PersianTimer.fixedWidth(PersianTimer.hour);
		}
		,Minute:function()
		{
			return PersianTimer.fixedWidth(PersianTimer.minute);
		}
		,Second:function()
		{
			return PersianTimer.fixedWidth(PersianTimer.second);
		}
		
		
};