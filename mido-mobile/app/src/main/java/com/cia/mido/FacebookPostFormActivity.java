package com.cia.mido;

import android.content.Intent;
import android.content.SharedPreferences;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.EditText;
import android.widget.TextView;

import com.facebook.AccessToken;
import com.facebook.AccessTokenTracker;
import com.facebook.CallbackManager;
import com.facebook.FacebookSdk;

import org.json.JSONException;
import org.json.JSONObject;

public class FacebookPostFormActivity extends AppCompatActivity {
    private AccessToken accessToken;
    private AccessTokenTracker accessTokenTracker;
    private CallbackManager callbackManager;
    private SharedPreferences sharedPreferences;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        FacebookSdk.sdkInitialize(getApplicationContext());
        callbackManager = CallbackManager.Factory.create();

        setContentView(R.layout.activity_facebook_post_form);

        getAccessToken();
        modifyFacebookPageText();
    }

    @Override
    public void onBackPressed() {
        Intent facebookPostsIntent = new Intent(this, FacebookPostsActivity.class);
        startActivity(facebookPostsIntent);
        finish();
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        callbackManager.onActivityResult(requestCode, resultCode, data);
    }

    private void modifyFacebookPageText() {
        TextView facebookPageText = (TextView) findViewById(R.id.facebookPageText);
        sharedPreferences = getSharedPreferences(getResources().getString(R.string.packageName), MODE_PRIVATE);
        String facebookPageName = sharedPreferences.getString("facebookPageName", null);
        facebookPageText.setText(facebookPageName);
    }

    public void createFacebookPagePost(View view) {
        String userId = sharedPreferences.getString("userId", null);
        String facebookPageId = sharedPreferences.getString("facebookPageId", null);
        String facebookPageCreatePostUrl = ((getResources().getString(R.string.facebookPageCreatePostUrl)).replace("{userId}", userId)).replace("{pageId}", facebookPageId);
        String url = getResources().getString(R.string.backEndUrl) + facebookPageCreatePostUrl;

        EditText facebookPagePost = (EditText) findViewById(R.id.facebookPagePost);
        String message = facebookPagePost.getText().toString();
        JSONObject request = new JSONObject();

        try {
            request.put("message", message);
            Client client = new Client(url, request, "POST");
            client.execute();
            JSONObject response;
            do {
                Thread.sleep(1000);
                response = client.getResponse();
            } while (response == null);
        } catch (Exception e) {
            e.printStackTrace();
        }

        Intent facebookPostsIntent = new Intent(this, FacebookPostsActivity.class);
        startActivity(facebookPostsIntent);
        finish();
    }

    private void getAccessToken() {
        accessTokenTracker = new AccessTokenTracker() {
            @Override
            protected void onCurrentAccessTokenChanged(AccessToken oldAccessToken, AccessToken currentAccessToken) {
                accessToken = currentAccessToken;
                if (accessToken == null) {
                    Intent facebookLoginIntent = new Intent(FacebookPostFormActivity.this, FacebookLoginActivity.class);
                    startActivity(facebookLoginIntent);
                    finish();
                }
            }
        };
        accessToken = AccessToken.getCurrentAccessToken();
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