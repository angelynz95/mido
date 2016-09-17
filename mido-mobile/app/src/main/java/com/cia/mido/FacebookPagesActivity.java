package com.cia.mido;

import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.widget.ArrayAdapter;
import android.widget.ListView;

import com.facebook.AccessToken;
import com.facebook.AccessTokenTracker;
import com.facebook.CallbackManager;
import com.facebook.FacebookSdk;

public class FacebookPagesActivity extends AppCompatActivity {
    private AccessToken accessToken;
    private AccessTokenTracker accessTokenTracker;
    private CallbackManager callbackManager;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        FacebookSdk.sdkInitialize(getApplicationContext());
        callbackManager = CallbackManager.Factory.create();

        setContentView(R.layout.activity_facebook_pages);

        getAccessToken();
        showFacebookPages();
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        callbackManager.onActivityResult(requestCode, resultCode, data);
    }

    @Override
    public void onDestroy() {
        super.onDestroy();
        accessTokenTracker.stopTracking();
    }

    @Override
    public void onBackPressed() {
        Intent socialMediaIntent = new Intent(this, SocialMediaActivity.class);
        startActivity(socialMediaIntent);
        finish();
    }

    private void getAccessToken() {
        accessTokenTracker = new AccessTokenTracker() {
            @Override
            protected void onCurrentAccessTokenChanged(AccessToken oldAccessToken, AccessToken currentAccessToken) {
                accessToken = currentAccessToken;
                if (accessToken == null) {
                    Intent facebookLoginIntent = new Intent(FacebookPagesActivity.this, FacebookLoginActivity.class);
                    startActivity(facebookLoginIntent);
                    finish();
                }
            }
        };
        accessToken = AccessToken.getCurrentAccessToken();
    }

    private void showFacebookPages() {
        String[] facebookPages = {"Mido", "CodeForGirl", "Anmategra"};
        ArrayAdapter adapter = new ArrayAdapter<String>(this, R.layout.facebook_page_listview, facebookPages);
        ListView facebookPageList = (ListView) findViewById(R.id.facebookPageList);
        facebookPageList.setAdapter(adapter);
    }
}
