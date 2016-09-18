package com.cia.mido;

import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;

import com.facebook.AccessToken;
import com.facebook.AccessTokenTracker;
import com.facebook.CallbackManager;
import com.facebook.FacebookSdk;

public class SocialMediaActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        FacebookSdk.sdkInitialize(getApplicationContext());

        setContentView(R.layout.activity_social_media);
    }

    @Override
    public void onBackPressed() {
        Intent menuIntent = new Intent(this, MenuActivity.class);
        startActivity(menuIntent);
        finish();
    }

    public void accessFacebook(View view) {
        AccessToken accessToken = AccessToken.getCurrentAccessToken();
        Intent facebookIntent;
        if (accessToken != null) {
            facebookIntent = new Intent(this, FacebookPagesActivity.class);
        } else {
            facebookIntent = new Intent(this, FacebookLoginActivity.class);
        }
        startActivity(facebookIntent);
        finish();
    }

    public void chooseSocialManager(View view) {
        Intent socialMediaIntent = new Intent(this, SocialMediaActivity.class);
        startActivity(socialMediaIntent);
        finish();
    }

    public void chooseNews(View view) {
        Intent newsIntent = new Intent(this, NewsActivity.class);
        startActivity(newsIntent);
        finish();
    }

    public void chooseMarketInsight(View view) {
        Intent marketIntent = new Intent(this, MarketInsightActivity.class);
        startActivity(marketIntent);
        finish();
    }

    public void chooseEmployeeSearch(View view) {
        Intent employeeSearchIntent = new Intent(this, EmployeeSearchActivity.class);
        startActivity(employeeSearchIntent);
        finish();

    }
}
